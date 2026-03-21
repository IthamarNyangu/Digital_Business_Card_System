<?php

use App\Core\Auth;

$isGuest = !Auth::check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($pageTitle ?? 'Admin') . ' | ' . config('app.app_name')) ?></title>
    <link rel="icon" type="image/png" href="<?= e(path_url('/media/logo1.png')) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(path_url('/assets/css/app.css')) ?>" rel="stylesheet">
</head>
<body class="admin-body<?= $isGuest ? ' admin-guest-body' : '' ?>">
<?php if (!$isGuest): ?>
    <nav class="navbar navbar-expand-lg app-navbar mb-4">
        <div class="container">
            <a class="navbar-brand fw-semibold d-inline-flex align-items-center gap-2" href="<?= e(path_url('/admin/dashboard')) ?>">
                <img src="<?= e(path_url('/media/logo1.png')) ?>" alt="Right to Care Zambia" class="brand-logo">
                <span>Admin Panel</span>
            </a>
            <div class="d-flex align-items-center gap-3 ms-auto">
                <a class="nav-link text-white-50" href="<?= e(path_url('/admin/dashboard')) ?>">Dashboard</a>
                <a class="nav-link text-white-50" href="<?= e(path_url('/admin/employees')) ?>">Employees</a>
                <span class="small text-white-50"><?= e(Auth::user()['username'] ?? '') ?></span>
                <form method="POST" action="<?= e(path_url('/admin/logout')) ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
                </form>
            </div>
        </div>
    </nav>
<?php endif; ?>

<main class="<?= $isGuest ? 'login-main' : 'container pb-5' ?>">
    <?php if ($successMessage): ?>
        <div class="alert alert-success shadow-sm"><?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-danger shadow-sm"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <?php if ($warningMessage): ?>
        <div class="alert alert-warning shadow-sm"><?= e($warningMessage) ?></div>
    <?php endif; ?>

    <?= $content ?>
</main>
</body>
</html>
