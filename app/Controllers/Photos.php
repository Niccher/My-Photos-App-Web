<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Photos extends BaseController
{
    public function index()
    {
        $photoModel = new \App\Models\PhotoModel();
        
        $data['photos'] = $photoModel->where('is_archived', false)->orderBy('taken_at', 'DESC')->findAll();
        
        // Calculate total storage
        $totalBytes = $photoModel->selectSum('size')->first()['size'] ?? 0;
        $data['storageUsed'] = $this->formatBytes($totalBytes);
        $data['storagePercent'] = min(100, ($totalBytes / (1024 * 1024 * 1024 * 1)) * 100); // Assume 1GB quota for display
        
        return view('photos/index', $data);
    }
    public function explore()
    {
        $photoModel = new \App\Models\PhotoModel();
        // Fetch only photos with location data
        $data['locations'] = $photoModel->where('latitude IS NOT NULL')->where('longitude IS NOT NULL')->where('is_archived', false)->findAll();
        
        // Pass same storage data as index
        $totalBytes = $photoModel->selectSum('size')->first()['size'] ?? 0;
        $data['storageUsed'] = $this->formatBytes($totalBytes);
        $data['storagePercent'] = min(100, ($totalBytes / (1024 * 1024 * 1024 * 1)) * 100);

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
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $count = 0;

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) continue;

            // Check if already in DB
            if ($photoModel->where('filename', $file)->first()) continue;

            $fullPath = $uploadPath . $file;
            $imageInfo = @getimagesize($fullPath);
            $gps = $this->getGps($fullPath);
            
            // Fallback for Mime type if getimagesize fails
            $mimeFallback = '';
            if (function_exists('mime_content_type')) {
                $mimeFallback = @mime_content_type($fullPath) ?: '';
            }

            $data = [
                'filename'       => $file,
                'path'           => 'uploads/' . $file,
                'mime_type'      => $imageInfo['mime'] ?? $mimeFallback,
                'width'          => $imageInfo[0] ?? null,
                'height'         => $imageInfo[1] ?? null,
                'size'           => filesize($fullPath),
                'taken_at'       => date('Y-m-d H:i:s', filemtime($fullPath)),
                'thumbnail_path' => 'thumbnails/' . $file,
                'latitude'       => $gps['lat'] ?? null,
                'longitude'      => $gps['lng'] ?? null,
                'exif_data'      => null,
            ];

            // Generate thumbnail placeholder if generation fails
            try {
                $this->generateThumbnail($fullPath, $thumbnailPath . $file);
            } catch (\Exception $e) {
                // Log and continue, photo will use default or broken image token
            }

            $photoModel->insert($data);
            $count++;
        }

        return $this->response->setJSON(['status' => 'success', 'message' => "Scanned and added $count new photos."]);
    }

    public function upload()
    {
        ini_set('memory_limit', '512M'); // Temporarily increase memory completely for high-res images

        $file = $this->request->getFile('file');

        if ($file === null || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => $file ? $file->getErrorString() : 'No file uploaded']);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads', $newName);

        $photoModel = new \App\Models\PhotoModel();
        $fullPath = FCPATH . 'uploads/' . $newName;
        $thumbnailPath = FCPATH . 'thumbnails/' . $newName;

        $imageInfo = @getimagesize($fullPath);
        $gps = $this->getGps($fullPath);

        $data = [
            'filename'       => $newName,
            'path'           => 'uploads/' . $newName,
            'mime_type'      => $imageInfo['mime'] ?? $file->getMimeType(),
            'width'          => $imageInfo[0] ?? null,
            'height'         => $imageInfo[1] ?? null,
            'size'           => @filesize($fullPath) ?: 0,
            'taken_at'       => date('Y-m-d H:i:s'),
            'thumbnail_path' => 'thumbnails/' . $newName,
            'latitude'       => $gps['lat'] ?? null,
            'longitude'      => $gps['lng'] ?? null,
            'exif_data'      => null,
        ];

        $this->generateThumbnail($fullPath, $thumbnailPath);
        $photoModel->insert($data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Uploaded successfully.', 'id' => $photoModel->getInsertID()]);
    }

    public function sharing()
    {
        return view('photos/sharing');
    }

    public function archive()
    {
        $photoModel = new \App\Models\PhotoModel();
        $data['photos'] = $photoModel->where('is_archived', true)->orderBy('taken_at', 'DESC')->findAll();
        return view('photos/archive', $data);
    }

    public function trash()
    {
        $photoModel = new \App\Models\PhotoModel();
        $data['photos'] = $photoModel->onlyDeleted()->orderBy('deleted_at', 'DESC')->findAll();
        return view('photos/trash', $data);
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

    private function getGps($path)
    {
        if (!function_exists('exif_read_data')) return null;

        try {
            $exif = @exif_read_data($path);
            if (isset($exif['GPSLatitude'], $exif['GPSLatitudeRef'], $exif['GPSLongitude'], $exif['GPSLongitudeRef'])) {
                $lat = $this->getGpsValue($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                $lng = $this->getGpsValue($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
                if ($lat !== null && $lng !== null) {
                    return ['lat' => $lat, 'lng' => $lng];
                }
            }
        } catch (\Exception $e) { }
        return null;
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
