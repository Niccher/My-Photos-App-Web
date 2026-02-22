<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Photos extends BaseController
{
    public function index()
    {
        $photoModel = new \App\Models\PhotoModel();
        
        // 1. Get counts and storage first (resets builder)
        $userId = auth()->id();
        $totalBytes = $photoModel->where('user_id', $userId)->selectSum('size')->first()['size'] ?? 0;
        $counts = $this->getSidebarCounts();

        // 2. Build the main query afresh
        $query = $photoModel->where('user_id', $userId)
                            ->where('is_archived', false)
                            ->orderBy('taken_at', 'DESC');

        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $query->groupStart()
                  ->like('filename', $q)
                  ->orLike('exif_data', $q)
                  ->orLike('taken_at', $q)
                  ->groupEnd();
        }

        $data = [
            'photos'         => $query->findAll(),
            'storageUsed'    => $this->formatBytes($totalBytes),
            'storagePercent' => min(100, ($totalBytes / (1024 * 1024 * 1024 * 1)) * 100),
            'counts'         => $counts,
            'searchQuery'    => $q
        ];
        
        return view('photos/index', $data);
    }
    public function explore()
    {
        $photoModel = new \App\Models\PhotoModel();
        $userId = auth()->id();

        // 1. Metrics first
        $totalBytes = $photoModel->where('user_id', $userId)->selectSum('size')->first()['size'] ?? 0;
        $counts = $this->getSidebarCounts();
        
        // 2. Main query
        $query = $photoModel->where('user_id', $userId)
                            ->where('latitude IS NOT NULL')
                            ->where('longitude IS NOT NULL')
                            ->where('is_archived', false);

        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $query->groupStart()
                  ->like('filename', $q)
                  ->orLike('exif_data', $q)
                  ->orLike('taken_at', $q)
                  ->groupEnd();
        }

        $data = [
            'locations'      => $query->findAll(),
            'storageUsed'    => $this->formatBytes($totalBytes),
            'storagePercent' => min(100, ($totalBytes / (1024 * 1024 * 1024 * 1)) * 100),
            'counts'         => $counts,
            'searchQuery'    => $q
        ];

        return view('photos/explore', $data);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function scan()
    {
        ini_set('memory_limit', '512M'); // Temporarily increase memory completely for high-res images

        $photoModel = new \App\Models\PhotoModel();
        $uploadPath = FCPATH . 'uploads/';
        $thumbnailPath = FCPATH . 'thumbnails/';

        $files = scandir($uploadPath);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'webm'];
        $count = 0;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) continue;

            // Check if already in DB
            if ($photoModel->where('filename', $file)->first()) continue;

            $fullPath = $uploadPath . $file;
            $isVideo = in_array($ext, ['mp4', 'mov', 'webm']);

            $imageInfo = $isVideo ? false : @getimagesize($fullPath);
            $metadata = $isVideo ? null : $this->getMergedMetadata($fullPath);

            $data = [
                'user_id'        => auth()->id(),
                'filename'       => $file,
                'path'           => 'uploads/' . $file,
                'mime_type'      => $mime,
                'width'          => $imageInfo ? $imageInfo[0] : null,
                'height'         => $imageInfo ? $imageInfo[1] : null,
                'size'           => filesize($fullPath),
                'taken_at'       => $metadata['taken_at'] ?? date('Y-m-d H:i:s', filemtime($fullPath)),
                'thumbnail_path' => 'thumbnails/' . $file,
                'latitude'       => $metadata['lat'] ?? null,
                'longitude'      => $metadata['lng'] ?? null,
                'exif_data'      => $metadata['exif'] ?? null,
            ];

            // Generate thumbnail placeholder if generation fails, or if it's a video
            if ($isVideo) {
                // For videos, point the thumbnail path to a default video icon we can create later, or just use the same file path and the frontend can handle it
                // We'll update the frontend to show a video icon if mime_type starts with video/
            } else {
                try {
                    $this->generateThumbnail($fullPath, $thumbnailPath . $file);
                } catch (\Exception $e) {
                    // Log and continue, photo will use default or broken image token
                }
            }

            $photoModel->insert($data);
            $count++;
        }

        return $this->response->setJSON(['status' => 'success', 'message' => "Scanned and added $count new photos."]);
    }

    public function upload()
    {
        ini_set('memory_limit', '512M');

        $file = $this->request->getFile('file');

        if ($file === null || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => $file ? $file->getErrorString() : 'No file uploaded']);
        }

        // Get info BEFORE move, as move() deletes the temporary file
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $newName = $file->getRandomName();
        
        $file->move(FCPATH . 'uploads', $newName);

        $photoModel = new \App\Models\PhotoModel();
        $fullPath = FCPATH . 'uploads/' . $newName;
        $thumbnailPath = FCPATH . 'thumbnails/' . $newName;

        $isVideo = strpos($mimeType, 'video/') === 0;
        $imageInfo = $isVideo ? false : @getimagesize($fullPath);
        $metadata = $isVideo ? null : $this->getMergedMetadata($fullPath);

        $data = [
            'user_id'        => auth()->id(),
            'filename'       => $newName,
            'path'           => 'uploads/' . $newName,
            'mime_type'      => $isVideo ? $mimeType : ($imageInfo['mime'] ?? $mimeType),
            'width'          => $imageInfo ? $imageInfo[0] : null,
            'height'         => $imageInfo ? $imageInfo[1] : null,
            'size'           => $size,
            'taken_at'       => $metadata['taken_at'] ?? date('Y-m-d H:i:s'),
            'thumbnail_path' => 'thumbnails/' . $newName,
            'latitude'       => $metadata['lat'] ?? null,
            'longitude'      => $metadata['lng'] ?? null,
            'exif_data'      => $metadata['exif'] ?? null,
        ];

        if (!$isVideo) {
            $this->generateThumbnail($fullPath, $thumbnailPath);
        }
        $photoModel->insert($data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Uploaded successfully.', 'id' => $photoModel->getInsertID()]);
    }

    public function sharing()
    {
        $photoModel = new \App\Models\PhotoModel();
        $linkModel = new \App\Models\SharedLinkModel();
        $shareModel = new \App\Models\PhotoShareModel();

        // 1. Photos I've shared via Public Links
        $publicShares = $linkModel->select('photos.*, shared_links.access_token')
            ->join('photos', 'photos.id = shared_links.photo_id')
            ->where('photos.user_id', auth()->id())
            ->findAll();

        // 2. Photos others shared WITH me
        $sharedWithMe = $shareModel->select('photos.*, photo_shares.permission')
            ->join('photos', 'photos.id = photo_shares.photo_id')
            ->where('photo_shares.shared_with', auth()->id())
            ->findAll();

        $data = [
            'publicShares' => $publicShares,
            'sharedWithMe' => $sharedWithMe,
            'counts'       => $this->getSidebarCounts()
        ];

        return view('photos/sharing', $data);
    }

    /**
     * Public Link Sharing
     */
    public function generateShareLink($id)
    {
        $photoModel = new \App\Models\PhotoModel();
        $shareModel = new \App\Models\SharedLinkModel();

        // Verify ownership
        $photo = $photoModel->where('user_id', auth()->id())->find($id);
        if (!$photo) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Photo not found']);
        }

        // Check for existing link
        $existing = $shareModel->where('photo_id', $id)->first();
        if ($existing) {
            $token = $existing['access_token'];
        } else {
            // Generate unique secure token
            $token = bin2hex(random_bytes(16));
            $shareModel->insert([
                'photo_id'     => $id,
                'access_token' => $token
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success', 
            'url'    => base_url("s/{$token}")
        ]);
    }

    public function viewShared($token)
    {
        $shareModel = new \App\Models\SharedLinkModel();
        $photoModel = new \App\Models\PhotoModel();

        $link = $shareModel->findByToken($token);
        if (!$link) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Link has expired or is invalid.");
        }

        $photo = $photoModel->find($link['photo_id']);
        if (!$photo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Shared photo no longer exists.");
        }

        return view('photos/view_shared', ['photo' => $photo]);
    }

    public function analytics()
    {
        $photoModel = new \App\Models\PhotoModel();
        $linkModel = new \App\Models\SharedLinkModel();
        $shareModel = new \App\Models\PhotoShareModel();
        $userId = auth()->id();

        // 1. Storage Stats
        $totalBytes = $photoModel->where('user_id', $userId)->selectSum('size')->first()['size'] ?? 0;
        $totalCount = $photoModel->where('user_id', $userId)->countAllResults();
        
        // 2. MIME Type Breakdown
        $mimeStats = $photoModel->where('user_id', $userId)
            ->select('mime_type, COUNT(*) as count')
            ->groupBy('mime_type')
            ->findAll();

        // 3. Monthly Activity (Current Year)
        $db = \Config\Database::connect();
        $monthlyQuery = $db->table('photos')
            ->select("DATE_FORMAT(taken_at, '%M') as month, COUNT(*) as count, MONTH(taken_at) as month_num")
            ->where('user_id', $userId)
            ->where('YEAR(taken_at)', date('Y'))
            ->groupBy('month, month_num')
            ->orderBy('month_num', 'ASC')
            ->get()
            ->getResultArray();

        // 4. Sharing Stats
        $publicShares = $linkModel->join('photos', 'photos.id = shared_links.photo_id')
                                   ->where('photos.user_id', $userId)->countAllResults();
        $internalShares = $shareModel->where('shared_by', $userId)->countAllResults();

        $data = [
            'totalBytes'     => $totalBytes,
            'totalCount'     => $totalCount,
            'storageUsed'    => $this->formatBytes($totalBytes),
            'storagePercent' => min(100, ($totalBytes / (1024 * 1024 * 1024 * 1)) * 100),
            'mimeStats'      => $mimeStats,
            'monthlyQuery'   => $monthlyQuery,
            'sharingStats'   => [
                'public'   => $publicShares,
                'internal' => $internalShares
            ],
            'counts'         => $this->getSidebarCounts()
        ];

        return view('photos/analytics', $data);
    }

    public function archive()
    {
        $photoModel = new \App\Models\PhotoModel();
        $userId = auth()->id();
        
        // 1. Counts first
        $counts = $this->getSidebarCounts();

        // 2. Main query afresh
        $query = $photoModel->where('user_id', $userId)->where('is_archived', true)->orderBy('taken_at', 'DESC');

        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $query->groupStart()
                  ->like('filename', $q)
                  ->orLike('exif_data', $q)
                  ->orLike('taken_at', $q)
                  ->groupEnd();
        }

        $data = [
            'photos'      => $query->findAll(),
            'counts'      => $counts,
            'searchQuery' => $q
        ];
        return view('photos/archive', $data);
    }

    public function trash()
    {
        $photoModel = new \App\Models\PhotoModel();
        $userId = auth()->id();
        
        $counts = $this->getSidebarCounts();
        
        $query = $photoModel->where('user_id', $userId)->onlyDeleted()->orderBy('deleted_at', 'DESC');

        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $query->groupStart()
                  ->like('filename', $q)
                  ->orLike('exif_data', $q)
                  ->orLike('taken_at', $q)
                  ->groupEnd();
        }

        $data = [
            'photos'      => $query->findAll(),
            'counts'      => $counts,
            'searchQuery' => $q
        ];
        return view('photos/trash', $data);
    }

    public function favorites()
    {
        $photoModel = new \App\Models\PhotoModel();
        $userId = auth()->id();
        
        $counts = $this->getSidebarCounts();
        
        $query = $photoModel->where('user_id', $userId)
                            ->where('is_favorite', true)
                            ->where('is_archived', false)
                            ->orderBy('taken_at', 'DESC');

        $q = $this->request->getGet('q');
        if (!empty($q)) {
            $query->groupStart()
                  ->like('filename', $q)
                  ->orLike('exif_data', $q)
                  ->orLike('taken_at', $q)
                  ->groupEnd();
        }

        $data = [
            'title'       => 'Favorites',
            'photos'      => $query->findAll(),
            'counts'      => $counts,
            'searchQuery' => $q
        ];
        return view('photos/index', $data); // We reuse index for simple filtered views
    }

    public function memories()
    {
        $photoModel = new \App\Models\PhotoModel();
        $userId = auth()->id();
        
        $counts = $this->getSidebarCounts();
        
        // Define milestones
        $today = date('m-d');
        $thisYear = date('Y');
        $sixMonthsDate = date('Y-m-d', strtotime('-6 months'));
        
        // Fetch photos taken on this day in past years
        $pastYearsPhotos = $photoModel->where('user_id', $userId)
                                      ->where('is_archived', false)
                                      ->where("DATE_FORMAT(taken_at, '%m-%d') =", $today)
                                      ->where("YEAR(taken_at) <", $thisYear)
                                      ->orderBy('taken_at', 'DESC')
                                      ->findAll();
                                      
        // Fetch photos from exactly 6 months ago
        $sixMonthsPhotos = $photoModel->where('user_id', $userId)
                                      ->where('is_archived', false)
                                      ->where("DATE(taken_at) =", $sixMonthsDate)
                                      ->orderBy('taken_at', 'DESC')
                                      ->findAll();

        $data = [
            'pastYearsPhotos' => $pastYearsPhotos,
            'sixMonthsPhotos' => $sixMonthsPhotos,
            'counts'          => $counts
        ];

        return view('photos/memories', $data);
    }

    public function albums()
    {
        $albumModel = new \App\Models\AlbumModel();
        $albums = $albumModel->getAlbumsWithThumbs(auth()->id());

        if ($this->request->getGet('json')) {
            return $this->response->setJSON(['albums' => $albums]);
        }

        $data = [
            'albums' => $albums,
            'counts' => $this->getSidebarCounts()
        ];
        return view('photos/albums', $data);
    }

    public function viewAlbum($id)
    {
        $albumModel = new \App\Models\AlbumModel();
        $album = $albumModel->where('user_id', auth()->id())->find($id);
        if (!$album) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $db = \Config\Database::connect();
        $photos = $db->table('album_photos')
                     ->join('photos', 'photos.id = album_photos.photo_id')
                     ->where('album_id', $id)
                     ->orderBy('added_at', 'DESC')
                     ->get()->getResultArray();

        $data = [
            'title'    => $album['name'],
            'subtitle' => $album['description'],
            'album'    => $album,
            'photos'   => $photos,
            'counts'   => $this->getSidebarCounts()
        ];
        return view('photos/index', $data); // Reuse gallery grid
    }

    public function createAlbum()
    {
        $userId = auth()->id();
        if (!$userId) return $this->response->setJSON(['status' => 'error', 'message' => 'Not authenticated']);

        $albumModel = new \App\Models\AlbumModel();
        $name = $this->request->getPost('name');
        if (empty($name)) return $this->response->setJSON(['status' => 'error', 'message' => 'Name is required']);

        $data = [
            'user_id'     => $userId,
            'name'        => $name,
            'description' => $this->request->getPost('description')
        ];

        if ($albumModel->insert($data)) {
            return $this->response->setJSON(['status' => 'success', 'id' => $albumModel->getInsertID()]);
        }
        
        return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to create album: ' . print_r($albumModel->errors(), true)]);
    }

    public function addPhotoToAlbum()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('album_photos');
        
        $albumId = $this->request->getPost('album_id');
        $photoId = $this->request->getPost('photo_id');

        // Check if already in album
        $exists = $builder->where(['album_id' => $albumId, 'photo_id' => $photoId])->get()->getRow();
        if ($exists) return $this->response->setJSON(['status' => 'error', 'message' => 'Already in album']);

        $builder->insert([
            'album_id' => $albumId,
            'photo_id' => $photoId,
            'added_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    public function toggleFavorite($id)
    {
        $photoModel = new \App\Models\PhotoModel();
        $photo = $photoModel->where('user_id', auth()->id())->find($id);
        if (!$photo) return $this->response->setJSON(['status' => 'error', 'message' => 'Photo not found']);

        $newVal = !$photo['is_favorite'];
        $photoModel->update($id, ['is_favorite' => $newVal]);

        return $this->response->setJSON(['status' => 'success', 'is_favorite' => $newVal]);
    }

    private function getSidebarCounts()
    {
        $photoModel = new \App\Models\PhotoModel();
        $linkModel = new \App\Models\SharedLinkModel();
        $shareModel = new \App\Models\PhotoShareModel();
        $userId = auth()->id();

        $photosCount = $photoModel->where('user_id', $userId)->where('is_archived', false)->countAllResults();
        $exploreCount = $photoModel->where('user_id', $userId)->where('is_archived', false)
                                   ->where('latitude IS NOT NULL')->where('longitude IS NOT NULL')->countAllResults();
        
        // Sharing count: Public links by me + Internal shares with me
        $publicLinkCount = $linkModel->join('photos', 'photos.id = shared_links.photo_id')
                                     ->where('photos.user_id', $userId)->countAllResults();
        $sharedWithMeCount = $shareModel->where('shared_with', $userId)->countAllResults();
        
        $archiveCount = $photoModel->where('user_id', $userId)->where('is_archived', true)->countAllResults();
        $trashCount = $photoModel->where('user_id', $userId)->onlyDeleted()->countAllResults();
        $favoritesCount = $photoModel->where('user_id', $userId)->where('is_favorite', true)->where('is_archived', false)->countAllResults();
        
        // Memories count: Photos taken on this day in past years or exactly 6 months ago
        $today = date('m-d');
        $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));
        
        $memoriesCount = $photoModel->where('user_id', $userId)
                                    ->where('is_archived', false)
                                    ->groupStart()
                                        ->where("DATE_FORMAT(taken_at, '%m-%d') =", $today)
                                        ->orWhere("DATE(taken_at) =", $sixMonthsAgo)
                                    ->groupEnd()
                                    ->countAllResults();
        
        $albumModel = new \App\Models\AlbumModel();
        $albumsCount = $albumModel->where('user_id', $userId)->countAllResults();

        return [
            'photos'    => $photosCount,
            'explore'   => $exploreCount,
            'sharing'   => $publicLinkCount + $sharedWithMeCount,
            'favorites' => $favoritesCount,
            'albums'    => $albumsCount,
            'memories'  => $memoriesCount,
            'archive'   => $archiveCount,
            'trash'     => $trashCount
        ];
    }

    public function archivePhoto($id)
    {
        $photoModel = new \App\Models\PhotoModel();
        $photo = $photoModel->find($id);
        if (!$photo) return $this->response->setJSON(['status' => 'error', 'message' => 'Photo not found']);
        
        $newStatus = !$photo['is_archived'];
        $photoModel->update($id, ['is_archived' => $newStatus]);
        return $this->response->setJSON(['status' => 'success', 'is_archived' => $newStatus]);
    }

    public function deletePhoto($id)
    {
        $photoModel = new \App\Models\PhotoModel();
        // If already deleted, force delete
        if ($photoModel->onlyDeleted()->find($id)) {
            $photo = $photoModel->onlyDeleted()->find($id);
            if ($photo) {
                @unlink(FCPATH . $photo['path']);
                @unlink(FCPATH . $photo['thumbnail_path']);
                $photoModel->delete($id, true); // purges
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Permanently deleted']);
        }
        
        // Otherwise, soft delete
        $photoModel->delete($id);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Moved to trash']);
    }

    public function restorePhoto($id)
    {
        $photoModel = new \App\Models\PhotoModel();
        // The model has useSoftDeletes = true, so update() works on the soft-deleted row?
        // Actually CI4 update doesn't automatically find soft-deleted items unless specifically told to,
        // or we can just set deleted_at to null directly via builder.
        $photoModel->builder()->where('id', $id)->update(['deleted_at' => null]);
        return $this->response->setJSON(['status' => 'success']);
    }

    public function migrate()
    {
        $migrate = \Config\Services::migrations();
        try {
            $migrate->latest();
            return "Migration successful";
        } catch (\Throwable $e) {
            return "Migration failed: " . $e->getMessage();
        }
    }

    public function bulkAction()
    {
        $userId  = auth()->id();
        $action  = $this->request->getPost('action');
        $photoIds = $this->request->getPost('ids'); // Expects array

        if (!$userId || empty($photoIds) || empty($action)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $photoModel = new \App\Models\PhotoModel();
        $db = \Config\Database::connect();

        switch ($action) {
            case 'archive':
                $photoModel->whereIn('id', $photoIds)->where('user_id', $userId)->set(['is_archived' => true])->update();
                break;
            case 'unarchive':
                $photoModel->whereIn('id', $photoIds)->where('user_id', $userId)->set(['is_archived' => false])->update();
                break;
            case 'favorite':
                $photoModel->whereIn('id', $photoIds)->where('user_id', $userId)->set(['is_favorite' => true])->update();
                break;
            case 'unfavorite':
                $photoModel->whereIn('id', $photoIds)->where('user_id', $userId)->set(['is_favorite' => false])->update();
                break;
            case 'delete':
                // Check if already in trash (soft delete) or permanent
                $photoModel->whereIn('id', $photoIds)->where('user_id', $userId)->delete();
                break;
            case 'add_to_album':
                $albumId = $this->request->getPost('album_id');
                if (!$albumId) return $this->response->setJSON(['status' => 'error', 'message' => 'Album ID required']);
                
                $builder = $db->table('album_photos');
                foreach ($photoIds as $id) {
                    $exists = $builder->where(['album_id' => $albumId, 'photo_id' => $id])->get()->getRow();
                    if (!$exists) {
                        $builder->insert([
                            'album_id' => $albumId,
                            'photo_id' => $id,
                            'added_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
                break;
        }

        return $this->response->setJSON(['status' => 'success']);
    }

    private function getMergedMetadata($path)
    {
        if (!function_exists('exif_read_data')) return null;

        $result = [
            'taken_at' => null,
            'lat'      => null,
            'lng'      => null,
            'exif'     => null
        ];

        try {
            $exif = @exif_read_data($path);
            if (!$exif) return $result;

            // 1. Extract Date
            if (isset($exif['DateTimeOriginal'])) {
                $result['taken_at'] = date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
            } elseif (isset($exif['DateTime'])) {
                $result['taken_at'] = date('Y-m-d H:i:s', strtotime($exif['DateTime']));
            }

            // 2. Extract GPS
            if (isset($exif['GPSLatitude'], $exif['GPSLatitudeRef'], $exif['GPSLongitude'], $exif['GPSLongitudeRef'])) {
                $result['lat'] = $this->getGpsValue($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                $result['lng'] = $this->getGpsValue($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
            }

            // 3. Store raw simplified EXIF
            $simplifiedExif = [];
            $allowedKeys = ['Make', 'Model', 'Software', 'ExposureTime', 'FNumber', 'ISOSpeedRatings', 'FocalLength', 'Flash'];
            foreach ($allowedKeys as $key) {
                if (isset($exif[$key])) $simplifiedExif[$key] = $exif[$key];
            }
            $result['exif'] = !empty($simplifiedExif) ? json_encode($simplifiedExif) : null;

        } catch (\Exception $e) { }

        return $result;
    }


    private function getGpsValue($coordinate, $ref)
    {
        if (!is_array($coordinate)) return null;
        
        $degrees = count($coordinate) > 0 ? $this->gpsToFloat($coordinate[0]) : 0;
        $minutes = count($coordinate) > 1 ? $this->gpsToFloat($coordinate[1]) : 0;
        $seconds = count($coordinate) > 2 ? $this->gpsToFloat($coordinate[2]) : 0;

        $flip = ($ref === 'W' || $ref === 'S') ? -1 : 1;
        return ($degrees + $minutes / 60 + $seconds / 3600) * $flip;
    }

    private function gpsToFloat($coordPart)
    {
        $parts = explode('/', $coordPart);
        if (count($parts) <= 0) return 0;
        if (count($parts) === 1) return (float)$parts[0];
        return (float)$parts[0] / (float)$parts[1];
    }

    private function generateThumbnail($source, $target)
    {
        if (file_exists($target)) return;

        try {
            // Using CodeIgniter's Image Library if available
            $config = [
                'image_library'  => 'gd2',
                'source_image'   => $source,
                'new_image'      => $target,
                'maintain_ratio' => true,
                'width'          => 400,
                'height'         => 400,
            ];

            $image = \Config\Services::image()
                ->withFile($source)
                ->resize(400, 400, true, 'height')
                ->save($target);
        } catch (\Exception $e) {
            // If the image type is unsupported by the GD2 library (like some PNGs/WEBPs without proper extensions),
            // just copy the original file as the thumbnail or leave it empty so the frontend uses the generic icon.
            @copy($source, $target); 
        }
    }
}
