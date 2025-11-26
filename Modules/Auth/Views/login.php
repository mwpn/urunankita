<?= $this->extend('Modules\Core\Views\layout') ?>

<?= $this->section('head') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-8">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-semibold text-gray-900">Masuk ke Akun</h2>
                <p class="mt-2 text-sm text-gray-600">Silakan login untuk melanjutkan</p>
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

            <form method="POST" action="/auth/login" class="space-y-6">
                <?= csrf_field() ?>
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-primary-500 focus:ring-primary-500"
                        placeholder="nama@example.com"
                    >
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
                        Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
