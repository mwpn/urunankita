<?php

namespace Modules\CampaignUpdate\Controllers;

use Modules\Core\Controllers\BaseController;
use Modules\CampaignUpdate\Services\CampaignUpdateService;

class CampaignUpdateController extends BaseController
{
    protected CampaignUpdateService $updateService;

    protected function initialize(): void
    {
        parent::initialize();
        $this->updateService = new CampaignUpdateService();
    }

    /**
     * Get updates by campaign (Laporan Kabar Terbaru)
     * GET /campaign-update/campaign/{campaignId}
     */
    public function getByCampaign($campaignId)
    {
        $filters = [
            'limit' => $this->request->getGet('limit') ?? 20,
        ];

        $updates = $this->updateService->getByCampaign((int) $campaignId, $filters);

        return $this->response->setJSON([
            'success' => true,
            'data' => $updates,
        ]);
    }

    /**
     * Create update (Buat Laporan Kabar Terbaru)
     * POST /campaign-update/create
     */
    public function create()
    {
        // Try both getPost and getVar to handle FormData
        $campaignId = $this->request->getPost('campaign_id') ?? $this->request->getVar('campaign_id');
        $content = $this->request->getPost('content') ?? $this->request->getVar('content');
        
        // Debug log
        log_message('debug', 'CampaignUpdate create - campaign_id: ' . var_export($campaignId, true));
        log_message('debug', 'CampaignUpdate create - content: ' . var_export($content, true));
        log_message('debug', 'CampaignUpdate create - all POST data: ' . json_encode($this->request->getPost()));
        log_message('debug', 'CampaignUpdate create - request method: ' . $this->request->getMethod());
        log_message('debug', 'CampaignUpdate create - content type: ' . $this->request->getHeaderLine('Content-Type'));
        
        $amountUsed = $this->request->getPost('amount_used') ?? $this->request->getVar('amount_used');
        if ($amountUsed !== null && $amountUsed !== '') {
            // Remove any non-numeric characters (except decimal point)
            $amountUsed = preg_replace('/[^0-9.]/', '', (string) $amountUsed);
            // Convert to float
            $amountUsed = (float) $amountUsed;
            // If result is 0 or invalid, set to null
            if ($amountUsed <= 0 || !is_numeric($amountUsed)) {
                $amountUsed = null;
            }
        } else {
            $amountUsed = null;
        }
        
        log_message('debug', 'CampaignUpdate create - amount_used processed: ' . var_export($amountUsed, true));

        // Handle file uploads directly (like CampaignController does)
        // This is more reliable than AJAX upload + JSON string
        $images = [];
        $tenantId = session()->get('tenant_id');
        
        // If no tenant_id in session (admin context), use platform tenant
        if (!$tenantId) {
            $db = \Config\Database::connect();
            $platform = $db->table('tenants')->where('slug', 'platform')->get()->getRowArray();
            if ($platform) {
                $tenantId = (int) $platform['id'];
            }
        }
        
        try {
            // Handle direct file uploads (if files are sent directly)
            // Use same approach as CampaignController
            $imagesFiles = $this->request->getFiles('images_files');
            if ($imagesFiles && isset($imagesFiles['images_files'])) {
                $storage = \Modules\File\Config\Services::storage();
                foreach ($imagesFiles['images_files'] as $imgFile) {
                    if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
                        $upload = $storage->upload($imgFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                        $images[] = '/uploads/' . ltrim($upload['path'], '/');
                    }
                }
            } else {
                // Fallback: try direct getFiles() approach
                $files = $this->request->getFiles();
                if (isset($files['images_files']) && is_array($files['images_files'])) {
                    $storage = \Modules\File\Config\Services::storage();
                    foreach ($files['images_files'] as $imgFile) {
                        if ($imgFile && $imgFile->isValid() && !$imgFile->hasMoved()) {
                            $upload = $storage->upload($imgFile, $tenantId, ['allowed_types' => ['jpg','jpeg','png','gif','webp']]);
                            $images[] = '/uploads/' . ltrim($upload['path'], '/');
                        }
                    }
                }
            }
            
            // Also check for images from POST (from AJAX upload)
            $imagesInput = $this->request->getPost('images');
            if ($imagesInput === null) {
                $imagesInput = $this->request->getVar('images');
            }
            
            if ($imagesInput !== null && $imagesInput !== '') {
                if (is_string($imagesInput)) {
                    $decoded = json_decode($imagesInput, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // Merge with direct uploads
                        $images = array_merge($images, $decoded);
                    }
                } elseif (is_array($imagesInput)) {
                    $images = array_merge($images, $imagesInput);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'CampaignUpdate upload failed: ' . $e->getMessage());
        }
        
        // Remove duplicates
        $images = array_unique($images);
        $images = array_values($images); // Re-index
        
        // Log for debugging
        log_message('debug', 'CampaignUpdate create - final images: ' . json_encode($images));
        log_message('debug', 'CampaignUpdate create - images count: ' . count($images));
        
        $data = [
            'campaign_id' => $campaignId,
            'title' => $this->request->getPost('title') ?? $this->request->getVar('title'),
            'content' => $content,
            'amount_used' => $amountUsed,
            'images' => $images, // Use parsed images array
            'youtube_url' => $this->request->getPost('youtube_url') ?? $this->request->getVar('youtube_url'),
            'is_pinned' => ($this->request->getPost('is_pinned') ?? $this->request->getVar('is_pinned')) ?? false,
        ];

        // Validate - check if empty or just whitespace
        if (empty($campaignId) || trim((string)$campaignId) === '') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID Urunan wajib diisi. Received: ' . var_export($campaignId, true),
                ])->setStatusCode(400);
            } else {
                return redirect()->back()->withInput()->with('error', 'ID Urunan wajib diisi');
            }
        }
        
        if (empty($content) || trim((string)$content) === '') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Konten laporan wajib diisi',
                ])->setStatusCode(400);
            } else {
                return redirect()->back()->withInput()->with('error', 'Konten laporan wajib diisi');
            }
        }

        try {
            $id = $this->updateService->create($data);

            if ($id) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Laporan Kabar Terbaru berhasil dibuat',
                        'data' => ['id' => $id],
                    ]);
                } else {
                    // Determine redirect URL based on referer or session
                    $referer = $this->request->getHeaderLine('Referer');
                    $redirectUrl = '/admin/reports';
                    
                    // Check if request came from tenant reports page
                    if (strpos($referer, '/tenant/reports') !== false) {
                        $redirectUrl = '/tenant/reports';
                    } elseif (strpos($referer, '/admin/reports') !== false) {
                        $redirectUrl = '/admin/reports';
                    } else {
                        // Fallback: check session for tenant_id
                        $tenantId = session()->get('tenant_id');
                        if ($tenantId) {
                            $redirectUrl = '/tenant/reports';
                        }
                    }
                    
                    return redirect()->to($redirectUrl)->with('success', 'Laporan penggunaan dana berhasil dibuat');
                }
            }

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal membuat Laporan Kabar Terbaru',
                ])->setStatusCode(500);
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal membuat laporan');
            }
        } catch (\Exception $e) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage(),
                ])->setStatusCode(400);
            } else {
                return redirect()->back()->withInput()->with('error', $e->getMessage());
            }
        }
    }

    /**
     * Update campaign update
     * POST /campaign-update/update/{id}
     */
    public function update($id)
    {
        $data = [];

        if ($this->request->getPost('title') !== null) {
            $data['title'] = $this->request->getPost('title');
        }
        if ($this->request->getPost('content') !== null) {
            $data['content'] = $this->request->getPost('content');
        }
        if ($this->request->getPost('amount_used') !== null) {
            $amountUsed = $this->request->getPost('amount_used');
            if ($amountUsed !== '' && $amountUsed !== null) {
                $data['amount_used'] = (float) $amountUsed;
            } else {
                $data['amount_used'] = null;
            }
        }
        if ($this->request->getPost('images') !== null) {
            $images = $this->request->getPost('images');
            $data['images'] = is_string($images) ? json_decode($images, true) : $images;
        }
        if ($this->request->getPost('youtube_url') !== null) {
            $data['youtube_url'] = $this->request->getPost('youtube_url');
        }
        if ($this->request->getPost('is_pinned') !== null) {
            $data['is_pinned'] = $this->request->getPost('is_pinned');
        }

        if (empty($data)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tidak ada data untuk diperbarui',
            ])->setStatusCode(400);
        }

        try {
            $result = $this->updateService->update((int) $id, $data);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Laporan Kabar Terbaru berhasil diperbarui',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }

    /**
     * Delete campaign update
     * DELETE /campaign-update/delete/{id}
     */
    public function delete($id)
    {
        try {
            $result = $this->updateService->delete((int) $id);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Laporan Kabar Terbaru berhasil dihapus',
                ]);
            }

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ])->setStatusCode(404);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage(),
            ])->setStatusCode(400);
        }
    }
}

