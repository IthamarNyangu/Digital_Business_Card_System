<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EmployeeController;
use App\Controllers\PublicContactController;
use App\Core\Auth;

$path = current_path();
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$authController = new AuthController();
$dashboardController = new DashboardController();
$employeeController = new EmployeeController();
$publicContactController = new PublicContactController();

try {
    // Keep routing explicit and easy to follow for small standalone projects.
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

    if ($method === 'GET' && $path === '/admin/dashboard') {
        $dashboardController->index();
        return;
    }

    if ($method === 'GET' && $path === '/admin/employees') {
        $employeeController->index();
        return;
    }

    if ($method === 'GET' && $path === '/admin/employees/create') {
        $employeeController->create();
        return;
    }

    if ($method === 'POST' && $path === '/admin/employees/create') {
        $employeeController->store();
        return;
    }

    if ($method === 'GET' && preg_match('#^/admin/employees/(\d+)/edit$#', $path, $matches)) {
        $employeeController->edit((int) $matches[1]);
        return;
    }

    if ($method === 'POST' && preg_match('#^/admin/employees/(\d+)/edit$#', $path, $matches)) {
        $employeeController->update((int) $matches[1]);
        return;
    }

    if ($method === 'POST' && preg_match('#^/admin/employees/(\d+)/deactivate$#', $path, $matches)) {
        $employeeController->deactivate((int) $matches[1]);
        return;
    }

    if ($method === 'POST' && preg_match('#^/admin/employees/(\d+)/activate$#', $path, $matches)) {
        $employeeController->activate((int) $matches[1]);
        return;
    }

    if ($method === 'GET' && preg_match('#^/admin/employees/(\d+)/qr$#', $path, $matches)) {
        $employeeController->downloadQr((int) $matches[1]);
        return;
    }

    if ($method === 'GET' && preg_match('#^/c/([a-f0-9]{32})/vcf$#', $path, $matches)) {
        $publicContactController->downloadVcf($matches[1]);
        return;
    }

    if ($method === 'GET' && preg_match('#^/c/([a-f0-9]{32})$#', $path, $matches)) {
        $publicContactController->show($matches[1]);
        return;
    }

    render('errors/404', [
        'pageTitle' => 'Page Not Found',
        'message' => 'The page you requested does not exist.',
    ], str_starts_with($path, '/admin') ? 'admin' : 'public', 404);
} catch (Throwable $exception) {
    render('errors/500', [
        'pageTitle' => 'Server Error',
        'message' => config('app.debug')
            ? $exception->getMessage()
            : 'Something went wrong while loading this page. Please check your configuration and database connection.',
    ], str_starts_with($path, '/admin') ? 'admin' : 'public', 500);
}
