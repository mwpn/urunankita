<?php

namespace Modules\File\Controllers;

use Modules\Core\Controllers\BaseController;
use Config\Services;
use Modules\File\Services\StorageService;
use Modules\File\Models\FileModel;

class FileController extends BaseController
{
    protected StorageService $storageService;
    protected FileModel $fileModel;

    protected function initialize(): void
    {
        parent::initialize();
        $this->storageService = Services::storage();
        $this->fileModel = new FileModel();
    }

    /**
     * Upload file
     * POST /file/upload
     */
    public function upload()
    {
        $tenantId = session()->get('tenant_id');
        
        // If no tenant_id in session (admin context), use platform tenant
        if (!$tenantId) {
            $db = \Config\Database::connect();
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            if ($platform) {
                $tenantId = (int) $platform['id'];
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tenant not found',
                ])->setStatusCode(401);
            }
        }

        $file = $this->request->getFile('file');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No file uploaded or file is invalid',
            ])->setStatusCode(400);
        }

        try {
            $options = [
                'max_size' => $this->request->getPost('max_size') ?? null,
                'allowed_types' => $this->request->getPost('allowed_types') ? 
                    explode(',', $this->request->getPost('allowed_types')) : null,
            ];

            $uploadResult = $this->storageService->upload($file, $tenantId, $options);

            // Save to database
            $fileData = [
                'original_name' => $uploadResult['original_name'],
                'filename' => $uploadResult['filename'],
                'path' => $uploadResult['path'],
                'full_path' => $uploadResult['full_path'],
                'size' => $uploadResult['size'],
                'mime_type' => $uploadResult['mime_type'],
                'extension' => $uploadResult['extension'],
                'tenant_id' => $tenantId,
                'user_id' => auth_user()['id'] ?? null,
                'folder' => $this->request->getPost('folder') ?? null,
                'type' => $this->request->getPost('type') ?? 'general',
                'description' => $this->request->getPost('description') ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->fileModel->insert($fileData);
            $fileData['id'] = $this->fileModel->getInsertID();

            return $this->response->setJSON([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => $fileData,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Download file
     * GET /file/download/{filename}
     */
    public function download($filename)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return redirect()->to('/auth/login');
        }

        // Security: Verify tenant owns this file
        $fileRecord = $this->fileModel->findByTenantAndFilename($filename, $tenantId);
        if (!$fileRecord) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File not found or access denied',
            ])->setStatusCode(404);
        }

        try {
            $file = $this->storageService->download($filename, $tenantId);
            
            if (!$file) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File not found',
                ])->setStatusCode(404);
            }

            return $this->response->download($file->getRealPath(), null);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * Delete file
     * DELETE /file/delete/{filename}
     */
    public function delete($filename)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        // Security: Verify tenant owns this file
        $fileRecord = $this->fileModel->findByTenantAndFilename($filename, $tenantId);
        if (!$fileRecord) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File not found or access denied',
            ])->setStatusCode(404);
        }

        try {
            $deleted = $this->storageService->delete($filename, $tenantId);
            
            if ($deleted) {
                // Remove from database
                $this->fileModel->delete($fileRecord['id']);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'File deleted successfully',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete file',
            ])->setStatusCode(500);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(500);
        }
    }

    /**
     * List files
     * GET /file/list
     */
    public function list()
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        $filters = [
            'user_id' => $this->request->getGet('user_id'),
            'type' => $this->request->getGet('type'),
            'folder' => $this->request->getGet('folder'),
            'limit' => $this->request->getGet('limit') ?? 50,
        ];

        // Remove null values
        $filters = array_filter($filters, fn($value) => $value !== null);

        $files = $this->fileModel->getByTenant($tenantId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $files,
        ]);
    }

    /**
     * Get file info
     * GET /file/info/{filename}
     */
    public function info($filename)
    {
        $tenantId = session()->get('tenant_id');
        if (!$tenantId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tenant not found',
            ])->setStatusCode(401);
        }

        // Security: Verify tenant owns this file
        $fileRecord = $this->fileModel->findByTenantAndFilename($filename, $tenantId);
        if (!$fileRecord) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File not found or access denied',
            ])->setStatusCode(404);
        }

        $fileInfo = $this->storageService->getFileInfo($filename, $tenantId);
        if (!$fileInfo) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File not found on disk',
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => array_merge($fileRecord, $fileInfo),
        ]);
    }
}

