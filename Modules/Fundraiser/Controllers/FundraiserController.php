<?php

namespace Modules\Fundraiser\Controllers;

use Modules\Fundraiser\Models\FundraiserApplicationModel;
use Modules\Public\Controllers\PublicController;

class FundraiserController extends PublicController
{
    protected FundraiserApplicationModel $applicationModel;

    public function __construct()
    {
        $this->applicationModel = new FundraiserApplicationModel();
    }

    public function showForm()
    {
        $data = [
            'title' => 'Pengajuan Penggalang Baru',
            'settings' => $this->getPlatformSettings(),
            'is_main_domain' => true,
        ];

        return view('Modules\\Fundraiser\\Views\\apply_form', $data);
    }

    public function submitForm()
    {
        $validationRules = [
            'full_name' => 'required|string|min_length[3]',
            'phone' => 'required|string|min_length[8]',
            'entity_type' => 'required|in_list[personal,foundation]',
            'youtube_channel' => 'permit_empty|valid_url',
            'instagram' => 'permit_empty|string',
            'twitter' => 'permit_empty|string',
            'facebook' => 'permit_empty|string',
            'reason' => 'required|string|min_length[20]',
            'ktp' => 'uploaded[ktp]|max_size[ktp,4096]|ext_in[ktp,png,jpg,jpeg,pdf]',
            'foundation_document.*' => 'permit_empty|max_size[foundation_document,4096]|ext_in[foundation_document,png,jpg,jpeg,pdf]',
        ];

        if ($this->request->getPost('entity_type') === 'foundation') {
            // Check if at least one foundation document is uploaded
            $foundationFiles = $this->request->getFiles();
            if (empty($foundationFiles['foundation_document']) || !is_array($foundationFiles['foundation_document'])) {
                return redirect()->back()->withInput()->with('errors', ['foundation_document' => 'Dokumen yayasan wajib diunggah minimal 1 file.']);
            }
            $hasValidFile = false;
            foreach ($foundationFiles['foundation_document'] as $file) {
                if ($file && $file->isValid()) {
                    $hasValidFile = true;
                    break;
                }
            }
            if (!$hasValidFile) {
                return redirect()->back()->withInput()->with('errors', ['foundation_document' => 'Dokumen yayasan wajib diunggah minimal 1 file.']);
            }
        }

        $validationMessages = [
            'full_name' => [
                'required' => 'Nama lengkap wajib diisi.',
                'min_length' => 'Nama lengkap minimal 3 karakter.',
            ],
            'phone' => [
                'required' => 'Nomor HP wajib diisi.',
                'min_length' => 'Nomor HP minimal 8 karakter.',
            ],
            'entity_type' => [
                'required' => 'Jenis penggalang wajib dipilih.',
                'in_list' => 'Jenis penggalang tidak valid.',
            ],
            'reason' => [
                'required' => 'Alasan ingin menjadi penggalang wajib diisi.',
                'min_length' => 'Alasan minimal 20 karakter.',
            ],
            'ktp' => [
                'uploaded' => 'File KTP wajib diunggah.',
                'max_size' => 'Ukuran file KTP maksimal 4 MB.',
                'ext_in' => 'Format file KTP harus JPG, PNG, atau PDF.',
            ],
            'foundation_document.*' => [
                'max_size' => 'Ukuran file dokumen yayasan maksimal 4 MB.',
                'ext_in' => 'Format file dokumen yayasan harus JPG, PNG, atau PDF.',
            ],
            'youtube_channel' => [
                'valid_url' => 'URL Channel Youtube tidak valid.',
            ],
        ];

        if (!$this->validate($validationRules, $validationMessages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $storage = \Modules\File\Config\Services::storage();
        // gunakan tenant platform (slug 'platform') atau fallback 1
        $platformTenant = \Config\Database::connect()->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platformTenant ? (int) $platformTenant['id'] : 1;
        $ktpPath = null;
        $foundationDocPath = null;

        $ktpFile = $this->request->getFile('ktp');
        if ($ktpFile && $ktpFile->isValid()) {
            $uploaded = $storage->upload($ktpFile, $platformTenantId, [
                'allowed_types' => ['png', 'jpg', 'jpeg', 'pdf'],
            ]);
            $ktpPath = '/uploads/' . ltrim($uploaded['path'], '/');
        }

        // Handle multiple foundation documents
        $foundationDocPaths = [];
        $foundationFiles = $this->request->getFiles();
        if (!empty($foundationFiles['foundation_document'])) {
            foreach ($foundationFiles['foundation_document'] as $foundationFile) {
                if ($foundationFile && $foundationFile->isValid()) {
                    $uploaded = $storage->upload($foundationFile, $platformTenantId, [
                        'allowed_types' => ['png', 'jpg', 'jpeg', 'pdf'],
                    ]);
                    $foundationDocPaths[] = '/uploads/' . ltrim($uploaded['path'], '/');
                }
            }
        }
        $foundationDocPath = !empty($foundationDocPaths) ? json_encode($foundationDocPaths) : null;

        $this->applicationModel->insert([
            'full_name' => $this->request->getPost('full_name'),
            'phone' => $this->request->getPost('phone'),
            'ktp_document' => $ktpPath,
            'entity_type' => $this->request->getPost('entity_type'),
            'foundation_document' => $foundationDocPath,
            'youtube_channel' => $this->request->getPost('youtube_channel'),
            'instagram' => $this->request->getPost('instagram'),
            'twitter' => $this->request->getPost('twitter'),
            'facebook' => $this->request->getPost('facebook'),
            'reason' => $this->request->getPost('reason'),
            'status' => 'pending',
        ]);

        return redirect()->to(base_url('page/penggalang-baru'))
            ->with('success', 'Pengajuan berhasil dikirim. Kami akan menghubungi Anda setelah proses verifikasi.');
    }

    public function adminIndex()
    {
        $applications = $this->applicationModel
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Pengajuan Penggalang',
            'applications' => $applications,
        ];

        return view('Modules\\Fundraiser\\Views\\admin_index', $data);
    }

    public function updateStatus(int $id)
    {
        $application = $this->applicationModel->find($id);
        if (!$application) {
            return redirect()->back()->with('error', 'Pengajuan tidak ditemukan.');
        }

        $status = $this->request->getPost('status');
        $notes = trim((string) $this->request->getPost('notes'));
        $allowedStatus = ['pending', 'reviewed', 'approved', 'rejected'];

        if (!in_array($status, $allowedStatus, true)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        $this->applicationModel->update($id, [
            'status' => $status,
            'notes' => $notes ?: null,
        ]);

        $statusLabel = [
            'pending' => 'dikembalikan ke Pending',
            'reviewed' => 'ditandai diproses',
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
        ];

        return redirect()->back()->with('success', 'Pengajuan ' . ($statusLabel[$status] ?? 'diperbarui') . '.');
    }
}

