<?php
// Get user data from session
$authUser = session()->get('auth_user') ?? [];
$userName = $authUser['name'] ?? 'User';
$userEmail = $authUser['email'] ?? '';

// Get user avatar from session
$userAvatar = $authUser['avatar'] ?? null;

// Format avatar path
if ($userAvatar) {
    // If it's already a full URL, use it as is
    if (preg_match('~^https?://~', $userAvatar)) {
        $headerAvatar = $userAvatar;
    } elseif (strpos($userAvatar, '/uploads/') === 0) {
        // Already starts with /uploads/, use base_url
        $headerAvatar = base_url($userAvatar);
    } else {
        // Add /uploads/ prefix and use base_url
        $headerAvatar = base_url('/uploads/' . ltrim($userAvatar, '/'));
    }
} else {
    // Default avatar
    $headerAvatar = base_url('admin-template/assets/avatars/face-1.jpg');
}

// Add timestamp to prevent caching
if (strpos($headerAvatar, '?') === false) {
    $headerAvatar .= '?v=' . time();
}

// Determine if admin or tenant
$currentUri = uri_string();
$isAdmin = (strpos($currentUri, '/admin/') === 0);
?>
<nav class="topnav navbar navbar-light">
    <button type="button" class="navbar-toggler text-muted mt-2 p-0 mr-3 collapseSidebar">
        <i class="fe fe-menu navbar-toggler-icon"></i>
    </button>
    <form class="form-inline mr-auto searchform text-muted">
        <input class="form-control mr-sm-2 bg-transparent border-0 pl-4 text-muted" type="search" placeholder="Type something..." aria-label="Search">
    </form>
    <ul class="nav">
        <li class="nav-item">
            <a class="nav-link text-muted my-2" href="#" id="modeSwitcher" data-mode="light">
                <i class="fe fe-sun fe-16"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-muted my-2" href="./#" data-toggle="modal" data-target=".modal-shortcut">
                <span class="fe fe-grid fe-16"></span>
            </a>
        </li>
        <li class="nav-item nav-notif">
            <a class="nav-link text-muted my-2" href="./#" data-toggle="modal" data-target=".modal-notif">
                <span class="fe fe-bell fe-16"></span>
                <span class="dot dot-md bg-success"></span>
            </a>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle text-muted pr-0" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="avatar avatar-sm mt-2">
                    <img id="headerAvatar" src="<?= esc($headerAvatar) ?>" alt="<?= esc($userName) ?>" class="avatar-img rounded-circle" style="width: 32px; height: 32px; object-fit: cover;" onerror="this.src='<?= base_url('admin-template/assets/avatars/face-1.jpg') ?>'">
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                <div class="dropdown-header">
                    <div class="d-flex align-items-center">
                        <img src="<?= esc($headerAvatar) ?>" alt="<?= esc($userName) ?>" class="rounded-circle mr-2" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.src='<?= base_url('admin-template/assets/avatars/face-1.jpg') ?>'">
                        <div>
                            <div class="font-weight-bold"><?= esc($userName) ?></div>
                            <small class="text-muted"><?= esc($userEmail) ?></small>
                        </div>
                    </div>
                </div>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= $isAdmin ? base_url('admin/profile/overview') : base_url('tenant/profile/overview') ?>">
                    <i class="fe fe-user fe-12 mr-2"></i>Profile
                </a>
                <a class="dropdown-item" href="<?= $isAdmin ? base_url('admin/profile/security') : base_url('tenant/profile/security') ?>">
                    <i class="fe fe-settings fe-12 mr-2"></i>Settings
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="<?= base_url('logout') ?>">
                    <i class="fe fe-log-out fe-12 mr-2"></i>Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
