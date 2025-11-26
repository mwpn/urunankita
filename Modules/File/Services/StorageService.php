<?php

namespace Modules\File\Services;

use CodeIgniter\Files\File;
use CodeIgniter\HTTP\Files\UploadedFile;

class StorageService
{
    protected string $basePath;
    protected array $allowedTypes = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
        'archive' => ['zip', 'rar', '7z'],
    ];
    protected int $maxFileSize = 5242880; // 5MB default

    public function __construct()
    {
        // Store uploads under public/uploads so files can be served directly
        $this->basePath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR;
        
        // Ensure base directory exists
        if (!is_dir($this->basePath)) {
            mkdir($this->basePath, 0755, true);
        }
    }

    /**
     * Get tenant upload directory
     *
     * @param int $tenantId
     * @return string
     */
    public function getTenantPath(int $tenantId): string
    {
        $path = $this->basePath . 'tenant_' . $tenantId . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
        
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
    }

    /**
     * Get full file path
     *
     * @param int $tenantId
     * @param string $filename
     * @return string
     */
    public function getFilePath(int $tenantId, string $filename): string
    {
        return $this->getTenantPath($tenantId) . $filename;
    }

    /**
     * Upload file
     *
     * @param UploadedFile $file
     * @param int $tenantId
     * @param array $options
     * @return array
     */
    public function upload(UploadedFile $file, int $tenantId, array $options = []): array
    {
        // Validate tenant
        if ($tenantId <= 0) {
            throw new \RuntimeException('Invalid tenant ID');
        }

        // Validate file
        if (!$file->isValid()) {
            throw new \RuntimeException($file->getErrorString());
        }

        // Check file size
        $maxSize = $options['max_size'] ?? $this->maxFileSize;
        if ($file->getSize() > $maxSize) {
            throw new \RuntimeException('File size exceeds maximum allowed size');
        }

        // Validate file type
        $allowedTypes = $options['allowed_types'] ?? array_merge(
            $this->allowedTypes['image'],
            $this->allowedTypes['document'],
            $this->allowedTypes['archive']
        );

        $extension = $file->getClientExtension();
        if (!in_array(strtolower($extension), array_map('strtolower', $allowedTypes))) {
            throw new \RuntimeException('File type not allowed');
        }

        // Generate unique filename
        $originalName = $file->getClientName();
        $newFilename = $this->generateFilename($originalName, $tenantId);

        // Get tenant directory
        $tenantPath = $this->getTenantPath($tenantId);

        // Move uploaded file
        if (!$file->move($tenantPath, $newFilename)) {
            throw new \RuntimeException('Failed to move uploaded file');
        }

        return [
            'original_name' => $originalName,
            'filename' => $newFilename,
            'path' => 'tenant_' . $tenantId . '/files/' . $newFilename,
            'full_path' => $tenantPath . $newFilename,
            'size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'extension' => $extension,
            'tenant_id' => $tenantId,
        ];
    }

    /**
     * Download file (with tenant isolation check)
     *
     * @param string $filename
     * @param int $tenantId
     * @return File|null
     */
    public function download(string $filename, int $tenantId): ?File
    {
        // Security: Validate tenant owns this file
        $filePath = $this->getFilePath($tenantId, $filename);

        if (!file_exists($filePath)) {
            return null;
        }

        // Double check path to prevent directory traversal
        $realPath = realpath($filePath);
        $realTenantPath = realpath($this->getTenantPath($tenantId));

        if (!$realPath || strpos($realPath, $realTenantPath) !== 0) {
            throw new \SecurityException('Invalid file access attempt');
        }

        return new File($filePath);
    }

    /**
     * Delete file (with tenant isolation check)
     *
     * @param string $filename
     * @param int $tenantId
     * @return bool
     */
    public function delete(string $filename, int $tenantId): bool
    {
        $filePath = $this->getFilePath($tenantId, $filename);

        if (!file_exists($filePath)) {
            return false;
        }

        // Security check
        $realPath = realpath($filePath);
        $realTenantPath = realpath($this->getTenantPath($tenantId));

        if (!$realPath || strpos($realPath, $realTenantPath) !== 0) {
            throw new \SecurityException('Invalid file deletion attempt');
        }

        return unlink($filePath);
    }

    /**
     * List files for a tenant
     *
     * @param int $tenantId
     * @param array $filters
     * @return array
     */
    public function listFiles(int $tenantId, array $filters = []): array
    {
        $tenantPath = $this->getTenantPath($tenantId);

        if (!is_dir($tenantPath)) {
            return [];
        }

        $files = [];
        $items = scandir($tenantPath);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $filePath = $tenantPath . $item;
            if (is_file($filePath)) {
                $files[] = [
                    'filename' => $item,
                    'size' => filesize($filePath),
                    'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                    'mime_type' => mime_content_type($filePath),
                ];
            }
        }

        return $files;
    }

    /**
     * Generate unique filename
     *
     * @param string $originalName
     * @param int $tenantId
     * @return string
     */
    protected function generateFilename(string $originalName, int $tenantId): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        
        // Format: tenantId_timestamp_randomhash.extension
        $timestamp = time();
        $random = bin2hex(random_bytes(4));
        
        return $tenantId . '_' . $timestamp . '_' . $random . '.' . strtolower($extension);
    }

    /**
     * Get file info
     *
     * @param string $filename
     * @param int $tenantId
     * @return array|null
     */
    public function getFileInfo(string $filename, int $tenantId): ?array
    {
        $filePath = $this->getFilePath($tenantId, $filename);

        if (!file_exists($filePath)) {
            return null;
        }

        // Security check
        $realPath = realpath($filePath);
        $realTenantPath = realpath($this->getTenantPath($tenantId));

        if (!$realPath || strpos($realPath, $realTenantPath) !== 0) {
            return null;
        }

        return [
            'filename' => $filename,
            'size' => filesize($filePath),
            'mime_type' => mime_content_type($filePath),
            'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
            'path' => 'tenant_' . $tenantId . '/files/' . $filename,
        ];
    }

    /**
     * Check if file exists and belongs to tenant
     *
     * @param string $filename
     * @param int $tenantId
     * @return bool
     */
    public function fileExists(string $filename, int $tenantId): bool
    {
        $filePath = $this->getFilePath($tenantId, $filename);

        if (!file_exists($filePath)) {
            return false;
        }

        // Security check
        $realPath = realpath($filePath);
        $realTenantPath = realpath($this->getTenantPath($tenantId));

        return $realPath && strpos($realPath, $realTenantPath) === 0;
    }
}

