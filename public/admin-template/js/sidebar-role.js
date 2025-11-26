/**
 * Sidebar Role Filter
 * Mengatur menu sidebar berdasarkan role user
 */

(function() {
  'use strict';
  
  // Set user role di sini
  // Bisa dari: localStorage, sessionStorage, atau dari backend
  // Contoh: 'admin', 'penggalang_dana', 'user'
  const userRole = window.userRole || 
                   localStorage.getItem('userRole') || 
                   sessionStorage.getItem('userRole') || 
                   'admin'; // Default: admin
  
  /**
   * Check apakah role user boleh akses menu tertentu
   * @param {string} menuRoles - Role yang diizinkan (format: "admin,penggalang_dana,user")
   * @param {string} userRole - Role user saat ini
   * @returns {boolean}
   */
  function hasAccess(menuRoles, userRole) {
    if (!menuRoles) return true; // Jika tidak ada data-role, tampilkan semua
    const roles = menuRoles.split(',').map(r => r.trim());
    return roles.includes(userRole);
  }
  
  /**
   * Filter menu sidebar berdasarkan role
   */
  function filterMenuByRole() {
    // Filter nav-heading
    document.querySelectorAll('.nav-heading[data-role]').forEach(heading => {
      if (!hasAccess(heading.getAttribute('data-role'), userRole)) {
        heading.style.display = 'none';
      } else {
        heading.style.display = '';
      }
    });
    
    // Filter ul dengan data-role
    document.querySelectorAll('ul.navbar-nav[data-role]').forEach(ul => {
      if (!hasAccess(ul.getAttribute('data-role'), userRole)) {
        ul.style.display = 'none';
      } else {
        ul.style.display = '';
        // Jika ul ditampilkan, pastikan nav-heading sebelumnya juga ditampilkan
        const prevHeading = ul.previousElementSibling;
        if (prevHeading && prevHeading.classList.contains('nav-heading')) {
          prevHeading.style.display = '';
        }
      }
    });
  }
  
  /**
   * Set role user dan filter ulang menu
   * @param {string} role - Role baru ('admin', 'penggalang_dana', 'user')
   */
  function setUserRole(role) {
    window.userRole = role;
    localStorage.setItem('userRole', role);
    sessionStorage.setItem('userRole', role);
    filterMenuByRole();
    console.log('User role changed to:', role);
  }
  
  // Jalankan filter saat DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', filterMenuByRole);
  } else {
    filterMenuByRole();
  }
  
  // Export untuk akses global
  window.filterSidebarByRole = setUserRole;
  window.getUserRole = function() {
    return window.userRole || localStorage.getItem('userRole') || 'admin';
  };
  
  // Debug: Log role saat ini
  console.log('Current user role:', userRole);
})();

