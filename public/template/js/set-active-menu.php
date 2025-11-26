<?php
// Set active menu item - Output as JavaScript
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Map page to href
$pageMap = [
    'dashboard' => 'index.html',
    'urunan-create' => 'urunan/create.html',
    'urunan-list' => 'urunan/list.html',
    'urunan-donasi' => 'urunan/donasi.html',
    'urunan-laporan' => 'urunan/laporan.html',
    'urunan-diskusi' => 'urunan/diskusi.html'
];

$activeHref = isset($pageMap[$page]) ? $pageMap[$page] : 'index.html';
header('Content-Type: application/javascript');
?>
// Set active menu item
(function() {
  const activeHref = '<?php echo addslashes($activeHref); ?>';
  setTimeout(function() {
    const sidebar = document.getElementById('leftSidebar');
    if (sidebar) {
      const activeLink = sidebar.querySelector('a[href="' + activeHref + '"]');
      if (activeLink) {
        // Remove active from all links
        sidebar.querySelectorAll('a.nav-link, a.navbar-brand').forEach(link => {
          link.classList.remove('active');
        });
        // Add active to current link
        activeLink.classList.add('active');
      }
    }
  }, 200);
})();

