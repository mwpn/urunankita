/**
 * Handle sidebar navigation - load content dynamically without page reload
 */
window.initSidebarNavigation = function() {
  const sidebar = document.getElementById('leftSidebar');
  if (!sidebar) {
    console.error('Sidebar not found');
    return;
  }

  // Get all navigation links in sidebar (including logo)
  const navLinks = sidebar.querySelectorAll('a.nav-link[href], a.navbar-brand[href]');
  
  navLinks.forEach(link => {
    // Skip if it's already absolute or special link
    const href = link.getAttribute('href');
    if (href.startsWith('http://') || href.startsWith('https://') || href.startsWith('#') || href.startsWith('mailto:')) {
      return;
    }
    
    // Add click handler
    link.addEventListener('click', function(e) {
      e.preventDefault();
      
      // Get content path - prefer data-content attribute, fallback to mapping from href
      let contentPath = link.getAttribute('data-content');
      
      if (!contentPath) {
        // Map href to content file
        // Dashboard -> dashboard-content.html
        if (href === 'index.html' || href === 'dashboard.html') {
          contentPath = 'includes/dashboard-content.html';
        }
        // Urunan create -> urunan-create-content.html
        else if (href === 'urunan/create.html') {
          contentPath = 'includes/urunan-create-content.html';
        }
        // Other urunan pages
        else if (href.startsWith('urunan/')) {
          const pageName = href.replace('urunan/', '').replace('.html', '');
          contentPath = `includes/urunan-${pageName}-content.html`;
        }
        // Admin pages
        else if (href.startsWith('admin/')) {
          const pageName = href.replace('admin/', '').replace(/\//g, '-').replace('.html', '');
          contentPath = `includes/admin-${pageName}-content.html`;
        }
        // Settings pages
        else if (href.startsWith('settings/')) {
          const pageName = href.replace('settings/', '').replace('.html', '');
          contentPath = `includes/settings-${pageName}-content.html`;
        }
        // Content pages
        else if (href.startsWith('content/')) {
          const pageName = href.replace('content/', '').replace('.html', '');
          contentPath = `includes/content-${pageName}-content.html`;
        }
        // Profile pages
        else if (href.startsWith('profile/')) {
          const pageName = href.replace('profile/', '').replace('.html', '');
          contentPath = `includes/profile-${pageName}-content.html`;
        }
        // Helpdesk pages
        else if (href.startsWith('helpdesk/')) {
          const pageName = href.replace('helpdesk/', '').replace('.html', '');
          contentPath = `includes/helpdesk-${pageName}-content.html`;
        }
        // Default: assume it's already a content path
        else if (!href.startsWith('includes/')) {
          contentPath = `includes/${href.replace('.html', '')}-content.html`;
        }
      }
      
      // Check if we're using PHP (index.php) or HTML (index.html)
      const isPHP = window.location.pathname.endsWith('.php') || window.location.pathname.endsWith('/');
      
      if (isPHP) {
        // For PHP: redirect to index.php with page parameter
        const pageName = contentPath.replace('includes/', '').replace('-content.html', '');
        window.location.href = 'index.php?page=' + pageName;
      } else {
        // For HTML: load content dynamically via AJAX
        if (window.loadContent) {
          window.loadContent(contentPath);
        } else {
          console.error('loadContent function not found');
        }
        
        // Update active state
        navLinks.forEach(l => l.classList.remove('active'));
        link.classList.add('active');
        
        // Update page title
        const pageTitle = link.querySelector('.item-text')?.textContent || 'Dashboard';
        document.title = pageTitle + ' - Urunankita';
        
        // Update URL without reload (optional, for browser history)
        if (window.history && window.history.pushState) {
          window.history.pushState({content: contentPath}, pageTitle, href);
        }
      }
    });
  });
};

