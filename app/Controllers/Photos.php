<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Photos extends BaseController
{
    public function index()
    {
        $photoModel = new \App\Models\PhotoModel();
        
        $data['photos'] = $photoModel->orderBy('taken_at', 'DESC')->findAll();
        
        // Calculate total storage
        $totalBytes = $photoModel->selectSum('size')->first()['size'] ?? 0;
        $data['storageUsed'] = $this->formatBytes($totalBytes);
        $data['storagePercent'] = min(100, ($totalBytes / (1024 * 1024 * 1024 * 1)) * 100); // Assume 1GB quota for display
        
        return view('photos/index', $data);
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
            $imageInfo = getimagesize($fullPath);
            
            $data = [
                'filename'       => $file,
                'path'           => 'uploads/' . $file,
                'mime_type'      => $imageInfo['mime'] ?? '',
                'width'          => $imageInfo[0] ?? null,
                'height'         => $imageInfo[1] ?? null,
                'size'           => filesize($fullPath),
                'taken_at'       => $this->getExifDate($fullPath) ?: date('Y-m-d H:i:s', filemtime($fullPath)),
                'thumbnail_path' => 'thumbnails/' . $file,
            ];

            // Generate thumbnail placeholder (actual generation below)
            $this->generateThumbnail($fullPath, $thumbnailPath . $file);

            $photoModel->insert($data);
            $count++;
        }

        return $this->response->setJSON(['status' => 'success', 'message' => "Scanned and added $count new photos."]);
    }

    public function upload()
    {
        $file = $this->request->getFile('file');

        if ($file === null || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => $file ? $file->getErrorString() : 'No file uploaded']);
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads', $newName);

        $photoModel = new \App\Models\PhotoModel();
        $fullPath = FCPATH . 'uploads/' . $newName;
        $thumbnailPath = FCPATH . 'thumbnails/' . $newName;

        $imageInfo = getimagesize($fullPath);

        $data = [
            'filename'       => $newName,
            'path'           => 'uploads/' . $newName,
            'mime_type'      => $imageInfo['mime'] ?? '',
            'width'          => $imageInfo[0] ?? null,
            'height'         => $imageInfo[1] ?? null,
            'size'           => filesize($fullPath),
            'taken_at'       => $this->getExifDate($fullPath) ?: date('Y-m-d H:i:s'),
            'thumbnail_path' => 'thumbnails/' . $newName,
        ];

        $this->generateThumbnail($fullPath, $thumbnailPath);
        $photoModel->insert($data);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Uploaded successfully.', 'id' => $photoModel->getInsertID()]);
    }

    private function getExifDate($path)
    {
        if (!function_exists('exif_read_data')) return null;
        
        try {
            $exif = @exif_read_data($path);
            if (isset($exif['DateTimeOriginal'])) {
                return date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal']));
            }
        } catch (\Exception $e) {
            // Log or ignore
        }
        return null;
    }

    private function generateThumbnail($source, $target)
    {
        if (file_exists($target)) return;

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
    }
}
