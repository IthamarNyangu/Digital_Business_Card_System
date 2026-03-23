<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EmployeeController;
use App\Core\Auth;

$path = current_path();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$authController = new AuthController();
$dashboardController = new DashboardController();
$employeeController = new EmployeeController();

try {
    if ($method === 'GET' && $path === '/') {
        Auth::check() ? redirect('/admin/dashboard') : redirect('/admin/login');
    }

    if ($method === 'GET' && $path === '/admin/login') {
        $authController->showLogin();
        return;
    }

    if ($method === 'POST' && $path === '/admin/login') {
        $authController->login();
        return;
    }

    if ($method === 'POST' && $path === '/admin/logout') {
        $authController->logout();
        return;
    }

    if ($method === 'POST' && $path === '/admin/session/ping') {
        $authController->ping();
        return;
    }

    if ($method === 'GET' && $path === '/admin/dashboard') {
        $dashboardController->index();
        return;
    }

    if ($method === 'GET' && $path === '/admin/import') {
        $employeeController->showImport();
        return;
    }

    if ($method === 'GET' && ($path === '/admin/contacts/create' || $path === '/admin/employees/create')) {
        $employeeController->showCreateForm();
        return;
    }

    if ($method === 'POST' && $path === '/admin/import') {
        $employeeController->import();
        return;
    }

    if ($method === 'POST' && ($path === '/admin/contacts/create' || $path === '/admin/employees/create')) {
        $employeeController->storeSingle();
        return;
    }

    if ($method === 'GET' && ($path === '/admin/results' || $path === '/admin/employees')) {
        $employeeController->index();
        return;
    }

    if ($method === 'GET' && preg_match('#^/admin/qrcodes/(\d+)/preview$#', $path, $matches)) {
        $employeeController->previewQr((int) $matches[1]);
        return;
    }

    if ($method === 'GET' && preg_match('#^/admin/qrcodes/(\d+)/download$#', $path, $matches)) {
        $employeeController->downloadQr((int) $matches[1]);
        return;
    }

    if ($method === 'GET' && $path === '/admin/qrcodes/download-all') {
        $employeeController->downloadAllZip();
        return;
    }

    if ($method === 'GET' && $path === '/admin/qrcodes/print-report') {
        $employeeController->printReport();
        return;
    }

    render('errors/404', [
        'pageTitle' => 'Page Not Found',
        'message' => 'The page you requested does not exist.',
    ], 'admin', 404);
} catch (Throwable $exception) {
    render('errors/500', [
        'pageTitle' => 'Server Error',
        'message' => config('app.debug')
            ? $exception->getMessage()
            : 'Something went wrong while loading this page. Please check your configuration and database connection.',
    ], 'admin', 500);
}
