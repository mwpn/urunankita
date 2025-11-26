/**
 * Contoh penggunaan untuk set user role
 * 
 * Copy kode ini ke halaman HTML atau set di backend setelah login
 */

// Set role dari localStorage (setelah login)
// Contoh: Set role admin
window.userRole = 'admin';
localStorage.setItem('userRole', 'admin');

// Atau set role penggalang_dana
// window.userRole = 'penggalang_dana';
// localStorage.setItem('userRole', 'penggalang_dana');

// Atau set role user/donatur
// window.userRole = 'user';
// localStorage.setItem('userRole', 'user');

// Setelah role di-set, filter sidebar
// (Ini akan otomatis jalan jika sidebar-role.js sudah di-load)
if (window.filterSidebarByRole) {
  window.filterSidebarByRole(window.userRole);
}

/**
 * Contoh: Set role dari backend (jika menggunakan PHP/Laravel)
 * 
 * Di PHP:
 * <script>
 *   window.userRole = '<?php echo $user->role; ?>';
 * </script>
 * 
 * Di Laravel Blade:
 * <script>
 *   window.userRole = '{{ Auth::user()->role }}';
 * </script>
 */

