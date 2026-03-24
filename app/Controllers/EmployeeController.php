<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Repositories\EmployeeRepository;
use App\Services\QrCodeService;
use App\Services\TransposedCsvImportService;
use App\Services\VcardService;
use App\Support\Validator;
use RuntimeException;
use Throwable;
use ZipArchive;

class EmployeeController
{
    public function showImport(): void
    {
        Auth::requireLogin();
        $this->ensureBulkSchemaReady();

        render('employees/import', [
            'pageTitle' => 'Bulk Import',
            'requiredFields' => array_values(array_map(
                static fn (array $field): string => $field['label'],
                array_filter(
                    TransposedCsvImportService::FIELD_MAP,
                    static fn (array $field): bool => ($field['required'] ?? true) === true
                )
            )),
            'optionalFields' => array_values(array_map(
                static fn (array $field): string => $field['label'],
                array_filter(
                    TransposedCsvImportService::FIELD_MAP,
                    static fn (array $field): bool => ($field['required'] ?? true) === false
                )
            )),
        ], 'admin');
    }

    public function showCreateForm(): void
    {
        Auth::requireLogin();
        $pdo = $this->ensureBulkSchemaReady();

        render('employees/form', [
            'pageTitle' => 'Add Single Contact',
            'supportsEmployeeNumber' => EmployeeRepository::supportsEmployeeNumber($pdo),
            'supportsSuffix' => EmployeeRepository::supportsSuffix($pdo),
            'employee' => [
                'employee_number' => '',
                'honorific' => '',
                'suffix' => '',
                'first_name' => '',
                'last_name' => '',
                'organization' => config('app.organisation_name'),
                'title' => '',
                'phone' => '',
                'email' => '',
                'street' => '',
                'city' => '',
                'region' => '',
                'postal_code' => '',
                'country' => 'Zambia',
            ],
        ], 'admin');
    }

    public function import(): void
    {
        Auth::requireLogin();
        Csrf::ensure();
        $this->ensureBulkSchemaReady();

        $uploadErrors = Validator::csvUpload($_FILES['csv_file'] ?? null);

        if ($uploadErrors !== []) {
            set_validation_errors($uploadErrors);
            redirect('/admin/import');
        }

        try {
            $parsed = (new TransposedCsvImportService())->parse((string) $_FILES['csv_file']['tmp_name']);
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            redirect('/admin/import');
        }

        $pdo = db();
        $vcardService = new VcardService();
        $qrCodeService = new QrCodeService();
        $summary = [
            'processed_columns' => count($parsed['employees']),
            'skipped_empty_columns' => (int) $parsed['skipped_empty_columns'],
            'success_count' => 0,
            'created_count' => 0,
            'failure_count' => 0,
            'results' => [],
            'failures' => [],
        ];

        foreach ($parsed['employees'] as $record) {
            $payload = Validator::importedEmployeePayload($record['data']);
            $errors = Validator::importedEmployee($payload);
            $fullName = trim(implode(' ', array_filter([
                $payload['honorific'] ?? '',
                $payload['first_name'],
                $payload['last_name'],
                $payload['suffix'] ?? '',
            ])));

            if ($errors !== []) {
                $summary['failure_count']++;
                $summary['failures'][] = [
                    'column_label' => $record['column_label'],
                    'name' => $fullName !== '' ? $fullName : 'Unnamed employee',
                    'message' => Validator::flattenErrors($errors),
                ];
                continue;
            }

            try {
                $result = EmployeeRepository::upsertContact($pdo, $payload);
                $employee = EmployeeRepository::find($pdo, (int) $result['id']);

                if (!$employee) {
                    throw new RuntimeException('The employee record could not be loaded after saving.');
                }

                $employeeForPayload = array_merge($employee, [
                    'honorific' => $payload['honorific'] ?? ($employee['honorific'] ?? null),
                    'suffix' => $payload['suffix'] ?? ($employee['suffix'] ?? null),
                ]);
                $contactPayload = $vcardService->build($employeeForPayload);
                $employeeForPayload['mecard_payload'] = $contactPayload;
                $qrCodePath = $qrCodeService->generateForEmployee($employeeForPayload);

                EmployeeRepository::updateGeneratedAssets($pdo, (int) $employee['id'], $contactPayload, $qrCodePath);

                $summary['success_count']++;
                $summary[$result['action'] . '_count']++;
                $summary['results'][] = [
                    'id' => (int) $employee['id'],
                    'column_label' => $record['column_label'],
                    'name' => trim(implode(' ', array_filter([
                        $employeeForPayload['honorific'] ?? '',
                        $employee['first_name'],
                        $employee['last_name'],
                        $employeeForPayload['suffix'] ?? '',
                    ]))),
                    'organization' => $employee['organization'],
                    'title' => $employee['title'],
                    'email' => $employee['email'],
                    'phone' => $employee['phone'],
                    'action' => $result['action'],
                    'qr_code_path' => $qrCodePath,
                ];
            } catch (Throwable $exception) {
                $summary['failure_count']++;
                $summary['failures'][] = [
                    'column_label' => $record['column_label'],
                    'name' => $fullName !== '' ? $fullName : 'Unnamed employee',
                    'message' => $exception->getMessage(),
                ];
            }
        }

        $_SESSION['import_summary'] = $summary;

        if ($summary['success_count'] > 0 && $summary['failure_count'] === 0) {
            flash('success', 'CSV import completed successfully. ' . $summary['success_count'] . ' QR code(s) are ready.');
        } elseif ($summary['success_count'] > 0) {
            flash('warning', 'CSV import finished with a few issues. Review the results summary below.');
        } else {
            flash('error', 'No QR codes were generated from the uploaded CSV.');
        }

        redirect('/admin/results');
    }

    public function index(): void
    {
        Auth::requireLogin();
        $pdo = $this->ensureBulkSchemaReady();

        $search = trim((string) ($_GET['search'] ?? ''));
        $perPage = 10;
        $requestedPage = (int) ($_GET['page'] ?? 1);
        $page = max(1, $requestedPage);
        $totalEmployees = EmployeeRepository::countFiltered($pdo, $search);
        $totalPages = max(1, (int) ceil($totalEmployees / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $importSummary = $_SESSION['import_summary'] ?? null;

        unset($_SESSION['import_summary']);

        render('employees/index', [
            'pageTitle' => 'QR Results',
            'employees' => EmployeeRepository::paginate($pdo, $perPage, $offset, $search),
            'search' => $search,
            'page' => $page,
            'perPage' => $perPage,
            'totalEmployees' => $totalEmployees,
            'totalPages' => $totalPages,
            'importSummary' => $importSummary,
        ], 'admin');
    }

    public function storeSingle(): void
    {
        Auth::requireLogin();
        Csrf::ensure();
        $pdo = $this->ensureBulkSchemaReady();

        $payload = Validator::importedEmployeePayload($_POST);
        $errors = Validator::importedEmployee($payload);

        if ($errors !== []) {
            remember_old_input($_POST);
            set_validation_errors($errors);
            redirect('/admin/contacts/create');
        }

        try {
            $result = EmployeeRepository::upsertContact($pdo, $payload);
            $employee = EmployeeRepository::find($pdo, (int) $result['id']);

            if (!$employee) {
                throw new RuntimeException('The contact could not be loaded after saving.');
            }

            $employeeForPayload = array_merge($employee, [
                'honorific' => $payload['honorific'] ?? ($employee['honorific'] ?? null),
                'suffix' => $payload['suffix'] ?? ($employee['suffix'] ?? null),
            ]);
            $contactPayload = (new VcardService())->build($employeeForPayload);
            $employeeForPayload['mecard_payload'] = $contactPayload;
            $qrCodePath = (new QrCodeService())->generateForEmployee($employeeForPayload);

            EmployeeRepository::updateGeneratedAssets($pdo, (int) $employee['id'], $contactPayload, $qrCodePath);

            clear_old_input();
            flash('success', 'Contact created successfully.');
            redirect('/admin/results');
        } catch (Throwable $exception) {
            remember_old_input($_POST);
            flash('error', $exception->getMessage());
            redirect('/admin/contacts/create');
        }
    }

    public function previewQr(int $id): void
    {
        Auth::requireLogin();
        $this->ensureBulkSchemaReady();

        $employee = $this->findEmployeeOrFail($id);
        $employee = $this->ensureQrAsset($employee);
        $absolutePath = base_path((string) $employee['qr_code_path']);

        header('Content-Type: image/png');
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('Content-Disposition: inline; filename="' . basename($absolutePath) . '"');
        readfile($absolutePath);
        exit;
    }

    public function downloadQr(int $id): void
    {
        Auth::requireLogin();
        $this->ensureBulkSchemaReady();

        $employee = $this->findEmployeeOrFail($id);
        $employee = $this->ensureQrAsset($employee);
        $absolutePath = base_path((string) $employee['qr_code_path']);

        header('Content-Type: image/png');
        header('Content-Length: ' . (string) filesize($absolutePath));
        header('Content-Disposition: attachment; filename="' . basename($absolutePath) . '"');
        readfile($absolutePath);
        exit;
    }

    public function downloadAllZip(): void
    {
        Auth::requireLogin();
        $this->ensureBulkSchemaReady();

        if (!class_exists(ZipArchive::class)) {
            flash('error', 'The PHP Zip extension is required to download all QR codes as a ZIP file.');
            redirect('/admin/results');
        }

        $employees = $this->preparedEmployeesForExport();

        if ($employees === []) {
            flash('error', 'There are no QR codes to include in the ZIP package yet.');
            redirect('/admin/results');
        }

        $temporaryZipPath = tempnam(sys_get_temp_dir(), 'rtcz_qr_');

        if ($temporaryZipPath === false) {
            flash('error', 'A temporary ZIP file could not be created.');
            redirect('/admin/results');
        }

        $zip = new ZipArchive();
        $opened = $zip->open($temporaryZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            @unlink($temporaryZipPath);
            flash('error', 'The ZIP archive could not be created.');
            redirect('/admin/results');
        }

        $addedFiles = 0;

        foreach ($employees as $employee) {
            $absolutePath = base_path((string) $employee['qr_code_path']);

            if (!is_file($absolutePath)) {
                continue;
            }

            $zip->addFile($absolutePath, 'qr-codes/' . basename($absolutePath));
            $addedFiles++;
        }

        $zip->addFromString('employee_list.csv', $this->buildEmployeeCsv($employees));
        $zip->addFromString('README.txt', $this->buildZipReadme($employees));

        $zip->close();

        if ($addedFiles === 0) {
            @unlink($temporaryZipPath);
            flash('error', 'No QR image files were available for the ZIP package.');
            redirect('/admin/results');
        }

        $downloadName = 'rtcz_qr_package_' . date('Ymd_His') . '.zip';

        header('Content-Type: application/zip');
        header('Content-Length: ' . (string) filesize($temporaryZipPath));
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        readfile($temporaryZipPath);
        @unlink($temporaryZipPath);
        exit;
    }

    public function printReport(): void
    {
        Auth::requireLogin();
        $this->ensureBulkSchemaReady();

        $employees = $this->preparedEmployeesForExport();

        if ($employees === []) {
            flash('error', 'There are no QR contacts available for the print report yet.');
            redirect('/admin/results');
        }

        render('employees/print', [
            'pageTitle' => 'Printable QR Report',
            'employees' => $employees,
            'generatedAt' => date('d M Y H:i'),
        ], 'print');
    }

    private function findEmployeeOrFail(int $id): array
    {
        $employee = EmployeeRepository::find(db(), $id);

        if ($employee) {
            return $employee;
        }

        render('errors/404', [
            'pageTitle' => 'Employee Not Found',
            'message' => 'The QR contact record you requested does not exist.',
        ], 'admin', 404);
        exit;
    }

    private function ensureQrAsset(array $employee): array
    {
        $qrCodePath = (string) ($employee['qr_code_path'] ?? '');
        $contactPayload = (string) ($employee['mecard_payload'] ?? '');
        $isVcardPayload = str_starts_with($contactPayload, 'BEGIN:VCARD');

        if ($isVcardPayload && $qrCodePath !== '' && is_file(base_path($qrCodePath))) {
            return $employee;
        }

        if (!$isVcardPayload) {
            $contactPayload = (new VcardService())->build($employee);
        }

        $employee['mecard_payload'] = $contactPayload;
        $qrCodePath = (new QrCodeService())->generateForEmployee($employee);
        EmployeeRepository::updateGeneratedAssets(db(), (int) $employee['id'], $contactPayload, $qrCodePath);
        $employee['qr_code_path'] = $qrCodePath;

        return $employee;
    }

    private function preparedEmployeesForExport(): array
    {
        $employees = EmployeeRepository::allForZip(db());
        $preparedEmployees = [];

        foreach ($employees as $employee) {
            try {
                $preparedEmployees[] = $this->ensureQrAsset($employee);
            } catch (RuntimeException $_exception) {
                continue;
            }
        }

        return $preparedEmployees;
    }

    private function buildEmployeeCsv(array $employees): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fputcsv($handle, [
            'ID',
            'Employee Number',
            'Honorific',
            'Suffix',
            'First Name',
            'Last Name',
            'Organization',
            'Title',
            'Phone',
            'Email',
            'Street',
            'City',
            'Region',
            'Postal Code',
            'Country',
            'QR File',
        ]);

        foreach ($employees as $employee) {
            fputcsv($handle, [
                $employee['id'],
                $employee['employee_number'] ?? '',
                $employee['honorific'] ?? '',
                $employee['suffix'] ?? '',
                $employee['first_name'],
                $employee['last_name'],
                $employee['organization'],
                $employee['title'],
                $employee['phone'],
                $employee['email'],
                $employee['street'],
                $employee['city'],
                $employee['region'],
                $employee['postal_code'],
                $employee['country'],
                basename((string) $employee['qr_code_path']),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    private function buildZipReadme(array $employees): string
    {
        return implode(PHP_EOL, [
            'RTCZ Bulk QR Contact Package',
            'Generated: ' . date('Y-m-d H:i:s'),
            'Total contacts: ' . count($employees),
            '',
            'Contents:',
            '- employee_list.csv contains the employee details and the matching QR filename.',
            '- qr-codes/ contains one vCard QR PNG per employee.',
            '',
            'Tip: open employee_list.csv in Excel if you need the contact register together with the QR image filenames.',
        ]);
    }

    private function ensureBulkSchemaReady(): \PDO
    {
        $pdo = db();
        $missingColumns = EmployeeRepository::missingBulkSchemaColumns($pdo);

        if ($missingColumns === []) {
            return $pdo;
        }

        render('errors/500', [
            'pageTitle' => 'Database Update Required',
            'message' => 'Your local database is still using the older QR-contact schema. Please import database/schema.sql again, optionally import database/seed.sql, and then recreate your admin account with scripts/create_admin.php. Missing columns: ' . implode(', ', $missingColumns) . '.',
        ], 'admin', 500);
        exit;
    }
}
