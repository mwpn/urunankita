<?= $this->extend('Modules\Core\Views\layout') ?>

<?= $this->section('head') ?>
<title>Daftar sebagai Penggalang - UrunanKita</title>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-semibold text-gray-900">Daftar sebagai Penggalang</h2>
                <p class="mt-2 text-sm text-gray-600">Buat akun penggalang urunan baru</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="/auth/register-tenant" class="space-y-6">
                <?= csrf_field() ?>
                
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Penggalang</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        value="<?= esc(old('name')) ?>"
                        class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="Contoh: Yayasan Peduli Sesama"
                    >
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">Slug</label>
                    <input 
                        type="text" 
                        id="slug" 
                        name="slug" 
                        required 
                        value="<?= esc(old('slug')) ?>"
                        class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="contoh: peduli-sesama"
                    >
                    <p class="mt-1 text-xs text-gray-500">URL Anda akan menjadi: {slug}.urunankita.id</p>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Owner</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="<?= esc(old('email')) ?>"
                        class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="owner@example.com"
                    >
                    <p class="mt-1 text-xs text-gray-500">Email ini akan digunakan sebagai akun owner tenant</p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="••••••••"
                    >
                </div>

                <div>
                    <button 
                        type="submit" 
                        class="w-full py-2.5 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-primary-600 text-white hover:bg-primary-700 focus:outline-none focus:bg-primary-700"
                    >
                        Daftar
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Sudah punya akun? 
                    <a href="/auth/login" class="text-primary-600 hover:text-primary-700 font-medium">Masuk di sini</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
