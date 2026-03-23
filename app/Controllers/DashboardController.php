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
        $missingColumns = EmployeeRepository::missingBulkSchemaColumns($pdo);

        if ($missingColumns !== []) {
            render('errors/500', [
                'pageTitle' => 'Database Update Required',
                'message' => 'Your local database is still using the older QR-contact schema. Please import database/schema.sql again, optionally import database/seed.sql, and then recreate your admin account with scripts/create_admin.php. Missing columns: ' . implode(', ', $missingColumns) . '.',
            ], 'admin', 500);
            return;
        }

        render('admin/dashboard', [
            'pageTitle' => 'Dashboard',
            'totalEmployees' => EmployeeRepository::countAll($pdo),
            'readyQrs' => EmployeeRepository::countWithQr($pdo),
            'organizationCount' => EmployeeRepository::countDistinctOrganizations($pdo),
            'recentEmployees' => EmployeeRepository::recent($pdo, 5),
        ], 'admin');
    }
}
