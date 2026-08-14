<?php
require_once __DIR__ . '/../config/app.php';
$pageTitle = $pageTitle ?? 'Agro Work';
$user = current_user();
$flashMessage = pull_flash();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Agro Work</title>
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>

<body>
    <div class="app-bg"></div>
    <?php if ($user): ?>
        <div class="app-shell">
            <aside class="sidebar">
                <a class="brand" href="<?= e(base_url('dashboard.php')) ?>">
                    <span class="brand-icon">A</span>
                    <span><strong>Agro Work</strong><small>Smart Workforce</small></span>
                </a>
                <div class="role-pill"><?= e(ucfirst($user['role'])) ?> Portal</div>
                <nav>
                    <a href="<?= e(base_url('dashboard.php')) ?>">Dashboard</a>
                    <?php if ($user['role'] === 'farmer'): ?>
                        <a href="<?= e(base_url('farmer/farms.php')) ?>">My Farms</a>
                        <a href="<?= e(base_url('farmer/jobs.php')) ?>">My Jobs</a>
                        <a href="<?= e(base_url('farmer/assignments.php')) ?>">Workers</a>
                        <a href="<?= e(base_url('farmer/payments.php')) ?>">Payments</a>
                    <?php elseif ($user['role'] === 'driver'): ?>
                        <a href="<?= e(base_url('driver/jobs.php')) ?>">Available Jobs</a>
                        <a href="<?= e(base_url('driver/assignments.php')) ?>">My Assignments</a>
                        <a href="<?= e(base_url('driver/payments.php')) ?>">Payments</a>
                    <?php else: ?>
                        <a href="<?= e(base_url('admin/users.php')) ?>">Users</a>
                        <a href="<?= e(base_url('admin/jobs.php')) ?>">Jobs</a>
                        <a href="<?= e(base_url('admin/job_types.php')) ?>">Job Types</a>
                    <?php endif; ?>
                    <a href="<?= e(base_url('profile.php')) ?>">Profile</a>
                </nav>
                <div class="sidebar-bottom">
                    <div class="user-mini"><strong><?= e($user['name']) ?></strong><small>ID #<?= e($user['person_id']) ?></small></div>
                    <a class="btn btn-soft full" href="<?= e(base_url('logout.php')) ?>">Logout</a>
                </div>
            </aside>
            <main class="main-content">
                <header class="topbar">
                    <div><small>Consumer-ready agricultural workforce platform</small><strong><?= e($pageTitle) ?></strong></div>
                    <span class="live-dot">● MySQL / XAMPP</span>
                </header>
                <section class="page-content">
                <?php else: ?>
                    <div class="public-wrap">
                    <?php endif; ?>

                    <?php if ($flashMessage): ?>
                        <div class="flash <?= e($flashMessage['type']) ?>" data-flash><?= e($flashMessage['message']) ?></div>
                    <?php endif; ?>