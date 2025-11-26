<?php

namespace Modules\Sponsorship\Controllers;

use Modules\Public\Controllers\PublicController;
use Modules\Sponsorship\Models\SponsorshipApplicationModel;
use Config\Services as BaseServices;

class SponsorshipController extends PublicController
{
    protected $applicationModel;

    public function __construct()
    {
        $this->applicationModel = new SponsorshipApplicationModel();
    }

    public function showForm()
    {
        $data = [
            'title' => 'Pengajuan Sponsorship',
            'settings' => $this->getPlatformSettings(),
            'is_main_domain' => true,
        ];

        return view('Modules\Sponsorship\Views\apply_form', $data);
    }

    public function submitForm()
    {
        $validationRules = [
            'company_name' => 'required|string|min_length[3]',
            'pic_name' => 'required|string|min_length[3]',
            'pic_position' => 'required|string|min_length[2]',
            'email' => 'required|valid_email',
            'phone' => 'required|string|min_length[8]',
            'address' => 'required|string|min_length[10]',
            'sponsor_type' => 'required|in_list[donasi,barang,jasa,kolaborasi]',
            'amount' => 'permit_empty|numeric',
            'public_visibility' => 'required|in_list[yes,no]',
            'reason' => 'required|string|min_length[20]',
            'logo' => 'permit_empty|max_size[logo,2048]|ext_in[logo,png,jpg,jpeg]',
            'partnership_letter' => 'permit_empty|max_size[partnership_letter,4096]|ext_in[partnership_letter,png,jpg,jpeg,pdf]',
            'company_profile' => 'permit_empty|max_size[company_profile,4096]|ext_in[company_profile,png,jpg,jpeg,pdf]',
        ];

        $validationMessages = [
            'company_name' => [
                'required' => 'Nama perusahaan wajib diisi.',
                'min_length' => 'Nama perusahaan minimal 3 karakter.',
            ],
            'pic_name' => [
                'required' => 'Nama PIC wajib diisi.',
                'min_length' => 'Nama PIC minimal 3 karakter.',
            ],
            'pic_position' => [
                'required' => 'Jabatan PIC wajib diisi.',
                'min_length' => 'Jabatan PIC minimal 2 karakter.',
            ],
            'email' => [
                'required' => 'Email wajib diisi.',
                'valid_email' => 'Format email tidak valid.',
            ],
            'phone' => [
                'required' => 'Nomor telepon wajib diisi.',
                'min_length' => 'Nomor telepon minimal 8 karakter.',
            ],
            'address' => [
                'required' => 'Alamat perusahaan wajib diisi.',
                'min_length' => 'Alamat perusahaan minimal 10 karakter.',
            ],
            'sponsor_type' => [
                'required' => 'Jenis sponsorship wajib dipilih.',
                'in_list' => 'Jenis sponsorship tidak valid.',
            ],
            'amount' => [
                'numeric' => 'Nominal harus berupa angka.',
            ],
            'public_visibility' => [
                'required' => 'Preferensi publikasi wajib dipilih.',
                'in_list' => 'Preferensi publikasi tidak valid.',
            ],
            'reason' => [
                'required' => 'Alasan ingin menjadi sponsor wajib diisi.',
                'min_length' => 'Alasan minimal 20 karakter.',
            ],
            'logo' => [
                'max_size' => 'Ukuran file logo maksimal 2 MB.',
                'ext_in' => 'Format file logo harus JPG atau PNG.',
            ],
            'partnership_letter' => [
                'max_size' => 'Ukuran file surat kerjasama maksimal 4 MB.',
                'ext_in' => 'Format file surat kerjasama harus JPG, PNG, atau PDF.',
            ],
            'company_profile' => [
                'max_size' => 'Ukuran file profil perusahaan maksimal 4 MB.',
                'ext_in' => 'Format file profil perusahaan harus JPG, PNG, atau PDF.',
            ],
        ];

        // Validate categories
        $categories = $this->request->getPost('categories');
        if (empty($categories) || !is_array($categories) || count($categories) === 0) {
            return redirect()->back()->withInput()->with('errors', ['categories' => 'Pilih minimal 1 kategori yang ingin didukung.']);
        }

        if (!$this->validate($validationRules, $validationMessages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $storage = \Modules\File\Config\Services::storage();
        $platformTenant = \Config\Database::connect()->table('tenants')->where('slug', 'platform')->get()->getRowArray();
        $platformTenantId = $platformTenant ? (int) $platformTenant['id'] : 1;

        $logoPath = null;
        $partnershipLetterPath = null;
        $companyProfilePath = null;

        $logoFile = $this->request->getFile('logo');
        if ($logoFile && $logoFile->isValid()) {
            $uploaded = $storage->upload($logoFile, $platformTenantId, [
                'allowed_types' => ['png', 'jpg', 'jpeg'],
            ]);
            $logoPath = '/uploads/' . ltrim($uploaded['path'], '/');
        }

        $partnershipFile = $this->request->getFile('partnership_letter');
        if ($partnershipFile && $partnershipFile->isValid()) {
            $uploaded = $storage->upload($partnershipFile, $platformTenantId, [
                'allowed_types' => ['png', 'jpg', 'jpeg', 'pdf'],
            ]);
            $partnershipLetterPath = '/uploads/' . ltrim($uploaded['path'], '/');
        }

        $profileFile = $this->request->getFile('company_profile');
        if ($profileFile && $profileFile->isValid()) {
            $uploaded = $storage->upload($profileFile, $platformTenantId, [
                'allowed_types' => ['png', 'jpg', 'jpeg', 'pdf'],
            ]);
            $companyProfilePath = '/uploads/' . ltrim($uploaded['path'], '/');
        }

        $categories = $this->request->getPost('categories');
        if (is_array($categories)) {
            $categories = json_encode($categories);
        }

        $this->applicationModel->insert([
            'company_name' => $this->request->getPost('company_name'),
            'pic_name' => $this->request->getPost('pic_name'),
            'pic_position' => $this->request->getPost('pic_position'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'website' => $this->request->getPost('website'),
            'address' => $this->request->getPost('address'),
            'sponsor_type' => $this->request->getPost('sponsor_type'),
            'amount' => $this->request->getPost('amount') ? (float) $this->request->getPost('amount') : null,
            'description' => $this->request->getPost('description'),
            'categories' => $categories,
            'public_visibility' => $this->request->getPost('public_visibility'),
            'logo' => $logoPath,
            'website_link' => $this->request->getPost('website_link'),
            'reason' => $this->request->getPost('reason'),
            'expectations' => $this->request->getPost('expectations'),
            'special_terms' => $this->request->getPost('special_terms'),
            'partnership_letter' => $partnershipLetterPath,
            'company_profile' => $companyProfilePath,
            'status' => 'pending',
        ]);

        return redirect()->to(base_url('page/sponsorship'))
            ->with('success', 'Pengajuan sponsorship berhasil dikirim. Kami akan menghubungi Anda setelah proses verifikasi.');
    }

    public function adminIndex()
    {
        $applications = $this->applicationModel->orderBy('created_at', 'DESC')->findAll();

        return view('Modules\Sponsorship\Views\admin_index', [
            'title' => 'Pengajuan Sponsorship',
            'applications' => $applications,
        ]);
    }

    public function updateStatus($id)
    {
        $application = $this->applicationModel->find($id);
        if (!$application) {
            return redirect()->back()->with('error', 'Pengajuan tidak ditemukan.');
        }

        $status = $this->request->getPost('status');
        $notes = $this->request->getPost('notes');

        $this->applicationModel->update($id, [
            'status' => $status,
            'notes' => $notes,
        ]);

        $statusLabels = [
            'approved' => 'disetujui',
            'rejected' => 'ditolak',
            'reviewed' => 'ditandai diproses',
        ];

        return redirect()->back()->with('success', 'Pengajuan berhasil ' . ($statusLabels[$status] ?? 'diperbarui') . '.');
    }
}
