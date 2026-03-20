<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\EmployeeRepository;
use App\Services\VCardService;

class PublicContactController
{
    public function show(string $token): void
    {
        $employee = EmployeeRepository::findByToken(db(), $token);

        if (!$employee) {
            render('errors/404', [
                'pageTitle' => 'Contact Not Found',
                'message' => 'The contact page you requested was not found.',
            ], 'public', 404);
            return;
        }

        if ($employee['status'] !== 'active') {
            render('public/inactive', [
                'pageTitle' => 'Contact Inactive',
                'employee' => $employee,
            ], 'public', 410);
            return;
        }

        render('public/contact', [
            'pageTitle' => trim($employee['first_name'] . ' ' . $employee['last_name']),
            'employee' => $employee,
        ], 'public');
    }

    public function downloadVcf(string $token): void
    {
        $employee = EmployeeRepository::findByToken(db(), $token);

        if (!$employee) {
            render('errors/404', [
                'pageTitle' => 'Contact Not Found',
                'message' => 'The contact page you requested was not found.',
            ], 'public', 404);
            return;
        }

        if ($employee['status'] !== 'active') {
            render('public/inactive', [
                'pageTitle' => 'Contact Inactive',
                'employee' => $employee,
            ], 'public', 410);
            return;
        }

        $filename = strtolower($employee['first_name'] . '-' . $employee['last_name'] . '.vcf');
        $filename = preg_replace('/[^a-z0-9\-]+/i', '-', $filename) ?: 'contact.vcf';

        header('Content-Type: text/vcard; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo (new VCardService())->build($employee);
        exit;
    }
}
