/**
 * Normalize sidebar links to always be relative from root (ci4/)
 * This fixes the issue where sidebar is loaded via fetch and relative paths
 * are relative to the calling page, not the sidebar file location
 */
window.normalizeSidebarLinks = function() {
  // Prevent multiple executions
  if (window.sidebarNormalized) {
    return;
  }
  window.sidebarNormalized = true;
  
  const sidebar = document.getElementById('leftSidebar');
  if (!sidebar) {
    console.error('Sidebar element not found.');
    return;
  }

  // Get current page path
  const currentPath = window.location.pathname;
  
  // Find ci4 in the path
  const ci4Index = currentPath.indexOf('/ci4/');
  if (ci4Index === -1) {
    // If ci4 not found, assume we're at root
    return;
  }
  
  // Get path after ci4/
  const pathAfterCi4 = currentPath.substring(ci4Index + 5); // +5 to skip '/ci4/'
  
  // Calculate depth: count directories (ignore filename)
  // For example:
  // - dashboard.html -> depth = 0 (no directories)
  // - urunan/create.html -> depth = 1 (one directory: urunan)
  // - admin/settings/general.html -> depth = 2 (two directories: admin/settings)
  const pathParts = pathAfterCi4.split('/').filter(p => p.length > 0);
  const depth = pathParts.length > 1 ? pathParts.length - 1 : 0;
  
  console.log('Normalize sidebar links:', {
    currentPath: currentPath,
    pathAfterCi4: pathAfterCi4,
    depth: depth
  });
  
  // Get all links in sidebar
  const links = sidebar.querySelectorAll('a[href]');
  
  links.forEach(link => {
    let href = link.getAttribute('href');
    const originalHref = href;
    
    // Skip if it's already absolute or starts with #
    if (href.startsWith('http://') || href.startsWith('https://') || href.startsWith('#') || href.startsWith('mailto:')) {
      return;
    }
    
    // URLs in sidebar are relative to root (ci4/)
    // We need to adjust them based on current page depth
    if (!href.startsWith('http://') && !href.startsWith('https://') && !href.startsWith('/')) {
      // If href starts with ./, remove it
      if (href.startsWith('./')) {
        href = href.substring(2);
      }
      
      // Skip if already has ../ (means it was already normalized)
      if (href.startsWith('../')) {
        return;
      }
      
      // If we're in a subdirectory, add ../ to go back to root
      if (depth > 0) {
        href = '../'.repeat(depth) + href;
      }
      
      // Update the href
      link.setAttribute('href', href);
      console.log('Normalized link:', originalHref, '->', href);
    }
  });
};

// Run after sidebar is loaded
// Don't use DOMContentLoaded because sidebar is loaded via fetch
// Instead, call this function after sidebar is injected into DOM

