<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Repositories\EmployeeRepository;

class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();

        $pdo = db();

        render('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'totalEmployees' => EmployeeRepository::countAll($pdo),
            'activeEmployees' => EmployeeRepository::countByStatus($pdo, 'active'),
            'inactiveEmployees' => EmployeeRepository::countByStatus($pdo, 'inactive'),
            'recentEmployees' => EmployeeRepository::recent($pdo, 5),
        ], 'admin');
    }
}
