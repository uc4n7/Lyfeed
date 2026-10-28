<?php
$pageTitle = isset($title) ? $title . ' | ' . config('app.name') : config('app.name');
$navUser   = $navUser ?? null;   // supplied by Controller::view(); views never query the database

// Presentation-only helpers for highlighting the active nav item.
$route     = $_GET['route'] ?? 'feed/index';
$onHome    = str_starts_with($route, 'feed');
$onSearch  = str_starts_with($route, 'search') && ($_GET['type'] ?? '') === 'posts';
$onUsers   = str_starts_with($route, 'search') && ($_GET['type'] ?? '') !== 'posts';
$onProfile = $navUser && str_starts_with($route, 'profile')
             && (!isset($_GET['id']) || (int)$_GET['id'] === (int)$navUser['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7c3aed">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= e(asset('img/favicon.svg')) ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= e(asset('img/favicon.png')) ?>">
    <link rel="shortcut icon" href="<?= e(base_url()) ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= e(asset('img/favicon.png')) ?>">

    <!-- Immediate theme application to prevent flash of unstyled content -->
    <script>
        (function() {
            var savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
                document.documentElement.setAttribute('data-bs-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                document.documentElement.setAttribute('data-bs-theme', 'light');
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(asset('css/style.css')) ?>?v=<?= filemtime(BASE_PATH . '/public/assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="<?= $navUser ? 'has-bottom-nav' : '' ?>" data-base-url="<?= e(base_url()) ?>">

<header class="topbar">
    <div class="topbar-inner">
        <a class="brand" href="<?= e(url($navUser ? 'feed/index' : 'auth/login')) ?>" aria-label="<?= e(config('app.name')) ?> home">
            <span class="brand-mark"><i class="bi bi-chat-heart-fill"></i></span>
            <span class="brand-text d-none d-sm-inline"><?= e(config('app.name')) ?></span>
        </a>

        <?php if ($navUser): ?>
            <form class="search-pill d-none d-md-block" action="<?= e(url()) ?>" method="get" role="search">
                <input type="hidden" name="route" value="search/index">
                <i class="bi bi-search"></i>
                <input type="search" name="q" placeholder="Search people or posts" aria-label="Search"
                       maxlength="100" value="<?= e($_GET['q'] ?? '') ?>" autocomplete="off">
            </form>

            <nav class="topnav" aria-label="Main">
                <a class="topnav-link d-none d-md-grid <?= $onHome ? 'active' : '' ?>" href="<?= e(url('feed/index')) ?>" title="Home" aria-label="Home">
                    <i class="bi <?= $onHome ? 'bi-house-heart-fill' : 'bi-house-heart' ?>"></i>
                </a>
                <a class="topnav-link d-none d-md-grid <?= $onSearch ? 'active' : '' ?>" href="<?= e(url('search/index', ['type' => 'posts'])) ?>" title="Discover Posts" aria-label="Discover Posts">
                    <i class="bi bi-compass<?= $onSearch ? '-fill' : '' ?>"></i>
                </a>
                <a class="topnav-link d-none d-md-grid <?= $onUsers ? 'active' : '' ?>" href="<?= e(url('search/index', ['type' => 'users'])) ?>" title="Discover People" aria-label="Discover People">
                    <i class="bi bi-people<?= $onUsers ? '-fill' : '' ?>"></i>
                </a>

                <!-- Dark mode toggle button in topbar -->
                <button type="button" class="topnav-link theme-toggle-btn border-0 bg-transparent d-none d-sm-grid" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <i class="bi bi-moon-stars"></i>
                </button>

                <div class="dropdown ms-1">
                    <button class="me-chip" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu">
                        <span class="avatar-ring"><img src="<?= e(avatar_url($navUser['profile_image'])) ?>" alt="" class="avatar avatar-sm"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
                        <li class="px-3 py-2">
                            <div class="fw-bold"><?= e($navUser['full_name']) ?></div>
                            <div class="text-muted small">@<?= e($navUser['username']) ?></div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= e(url('profile/show', ['id' => $navUser['id']])) ?>"><i class="bi bi-person-circle me-2"></i>My profile</a></li>
                        <li><a class="dropdown-item" href="<?= e(url('profile/edit')) ?>"><i class="bi bi-sliders me-2"></i>Edit profile</a></li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center justify-content-between" id="themeToggleDropdownBtn">
                                <span><i class="bi bi-moon-stars me-2"></i>Dark mode</span>
                                <span class="badge bg-secondary-subtle text-secondary" id="themeStatusBadge">Off</span>
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="<?= e(url('auth/logout')) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i>Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
        <?php else: ?>
            <nav class="topnav">
                <button type="button" class="topnav-link theme-toggle-btn border-0 bg-transparent me-2" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <i class="bi bi-moon-stars"></i>
                </button>
                <a class="btn btn-sm btn-outline-secondary px-3" href="<?= e(url('auth/login')) ?>">Log in</a>
                <a class="btn btn-sm btn-primary px-3" href="<?= e(url('auth/register')) ?>">Sign up</a>
            </nav>
        <?php endif; ?>
    </div>
</header>

<main class="container-xl app-main py-4">
    <?php require BASE_PATH . '/app/views/partials/flash.php'; ?>
