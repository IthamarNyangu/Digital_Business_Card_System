<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\EmployeeRepository;
use App\Services\QrCodeService;
use App\Support\Validator;
use RuntimeException;

class EmployeeController
{
    public function index(): void
    {
        Auth::requireLogin();

        $pdo = db();
        $search = trim((string) ($_GET['search'] ?? ''));
        $perPage = 5;
        $requestedPage = (int) ($_GET['page'] ?? 1);
        $page = max(1, $requestedPage);
        $totalEmployees = EmployeeRepository::countFiltered($pdo, $search);
        $totalPages = max(1, (int) ceil($totalEmployees / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        render('employees/index', [
            'pageTitle' => 'Employees',
            'employees' => EmployeeRepository::paginate($pdo, $perPage, $offset, $search),
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalEmployees' => $totalEmployees,
            'totalPages' => $totalPages,
        ], 'admin');
    }

    public function create(): void
    {
        Auth::requireLogin();

        render('employees/form', [
            'pageTitle' => 'Add Employee',
            'formTitle' => 'Add Employee',
            'submitLabel' => 'Save Employee',
            'action' => '/admin/employees/create',
            'employee' => [
                'first_name' => '',
                'last_name' => '',
                'position' => '',
                'department' => '',
                'phone' => '',
                'email' => '',
                'location' => '',
                'status' => 'active',
            ],
            'isEdit' => false,
        ], 'admin');
    }

    public function store(): void
    {
        Auth::requireLogin();
        Csrf::ensure();

        $payload = Validator::employeePayload($_POST);
        $errors = Validator::employee($payload);

        if ($errors !== []) {
            remember_old_input($_POST);
            set_validation_errors($errors);
            redirect('/admin/employees/create');
        }

        $pdo = db();
        $payload['public_token'] = $this->generateUniqueToken();
        $payload['qr_code_path'] = null;
        $payload['status'] = 'active';

        $employeeId = EmployeeRepository::create($pdo, $payload);
        $employee = EmployeeRepository::find($pdo, $employeeId);

        $this->tryGenerateQrCode($employee);

        flash('success', 'Employee created successfully.');
        redirect('/admin/employees');
    }

    public function edit(int $id): void
    {
        Auth::requireLogin();

        $employee = EmployeeRepository::find(db(), $id);

        if (!$employee) {
            render('errors/404', [
                'pageTitle' => 'Employee Not Found',
                'message' => 'The employee you are trying to edit does not exist.',
            ], 'admin', 404);
            return;
        }

        render('employees/form', [
            'pageTitle' => 'Edit Employee',
            'formTitle' => 'Edit Employee',
            'submitLabel' => 'Update Employee',
            'action' => '/admin/employees/' . $employee['id'] . '/edit',
            'employee' => $employee,
            'isEdit' => true,
        ], 'admin');
    }

    public function update(int $id): void
    {
        Auth::requireLogin();
        Csrf::ensure();

        $pdo = db();
        $employee = EmployeeRepository::find($pdo, $id);

        if (!$employee) {
            render('errors/404', [
                'pageTitle' => 'Employee Not Found',
                'message' => 'The employee you are trying to update does not exist.',
            ], 'admin', 404);
            return;
        }

        $payload = Validator::employeePayload($_POST);
        $errors = Validator::employee($payload);

        if ($errors !== []) {
            remember_old_input($_POST);
            set_validation_errors($errors);
            redirect('/admin/employees/' . $id . '/edit');
        }

        EmployeeRepository::update($pdo, $id, $payload);

        $updatedEmployee = EmployeeRepository::find($pdo, $id);

        if (($updatedEmployee['status'] ?? 'inactive') === 'active') {
            $this->tryGenerateQrCode($updatedEmployee);
        }

        flash('success', 'Employee updated successfully.');
        redirect('/admin/employees');
    }

    public function deactivate(int $id): void
    {
        Auth::requireLogin();
        Csrf::ensure();

        $employee = EmployeeRepository::find(db(), $id);

        if (!$employee) {
            render('errors/404', [
                'pageTitle' => 'Employee Not Found',
                'message' => 'The employee you are trying to deactivate does not exist.',
            ], 'admin', 404);
            return;
        }

        EmployeeRepository::deactivate(db(), $id);
        flash('success', 'Employee marked as inactive.');
        redirect('/admin/employees');
    }

    public function activate(int $id): void
    {
        Auth::requireLogin();
        Csrf::ensure();

        $pdo = db();
        $employee = EmployeeRepository::find($pdo, $id);

        if (!$employee) {
            render('errors/404', [
                'pageTitle' => 'Employee Not Found',
                'message' => 'The employee you are trying to activate does not exist.',
            ], 'admin', 404);
            return;
        }

        EmployeeRepository::activate($pdo, $id);

        $updatedEmployee = EmployeeRepository::find($pdo, $id);
        $this->tryGenerateQrCode($updatedEmployee);

        flash('success', 'Employee marked as active.');
        redirect('/admin/employees');
    }

    public function downloadQr(int $id): void
    {
        Auth::requireLogin();

        $pdo = db();
        $employee = EmployeeRepository::find($pdo, $id);

        if (!$employee) {
            render('errors/404', [
                'pageTitle' => 'Employee Not Found',
                'message' => 'The employee does not exist.',
            ], 'admin', 404);
            return;
        }

        if (($employee['status'] ?? 'inactive') !== 'active') {
            flash('error', 'Inactive employees do not have downloadable QR codes.');
            redirect('/admin/employees');
        }

        $relativePath = $employee['qr_code_path'];

        if (!$relativePath || !is_file(base_path($relativePath))) {
            $relativePath = $this->regenerateQr($employee);
        }

        $absolutePath = base_path($relativePath);

        header('Content-Type: image/png');
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('Content-Disposition: attachment; filename="employee-' . $employee['id'] . '-qr.png"');
        readfile($absolutePath);
        exit;
    }

    private function tryGenerateQrCode(?array $employee): void
    {
        if (!$employee || ($employee['status'] ?? 'inactive') !== 'active') {
            return;
        }

        try {
            $relativePath = (new QrCodeService())->generateForEmployee($employee);
            EmployeeRepository::updateQrCodePath(db(), (int) $employee['id'], $relativePath);
        } catch (RuntimeException $exception) {
            flash('warning', 'Employee saved, but QR code generation was skipped: ' . $exception->getMessage());
        }
    }

    private function regenerateQr(array $employee): string
    {
        try {
            $relativePath = (new QrCodeService())->generateForEmployee($employee);
            EmployeeRepository::updateQrCodePath(db(), (int) $employee['id'], $relativePath);

            return $relativePath;
        } catch (RuntimeException $exception) {
            flash('error', 'QR code could not be generated: ' . $exception->getMessage());
            redirect('/admin/employees');
        }
    }

    private function generateUniqueToken(): string
    {
        $pdo = db();

        do {
            $token = bin2hex(random_bytes(16));
        } while (EmployeeRepository::tokenExists($pdo, $token));

        return $token;
    }
}
