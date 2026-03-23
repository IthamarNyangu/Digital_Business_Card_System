<?php

declare(strict_types=1);

namespace App\Support;

class Validator
{
    public static function login(array $input): array
    {
        $errors = [];

        if (trim((string) ($input['username'] ?? '')) === '') {
            $errors['username'] = 'Username is required.';
        }

        if (trim((string) ($input['password'] ?? '')) === '') {
            $errors['password'] = 'Password is required.';
        }

        return $errors;
    }

    public static function csvUpload(?array $file): array
    {
        if (!$file || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['csv_file' => 'Please choose a CSV file to import.'];
        }

        if ((int) ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['csv_file' => 'The CSV file could not be uploaded. Please try again.'];
        }

        $name = (string) ($file['name'] ?? '');
        $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if ($extension !== 'csv') {
            return ['csv_file' => 'Please upload a file saved in CSV format.'];
        }

        if ((int) ($file['size'] ?? 0) <= 0) {
            return ['csv_file' => 'The uploaded CSV file is empty.'];
        }

        return [];
    }

    public static function importedEmployeePayload(array $input): array
    {
        return [
            'employee_number' => self::nullableString($input['employee_number'] ?? null),
            'first_name' => trim((string) ($input['first_name'] ?? '')),
            'last_name' => trim((string) ($input['last_name'] ?? '')),
            'organization' => trim((string) ($input['organization'] ?? '')),
            'title' => trim((string) ($input['title'] ?? '')),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'street' => trim((string) ($input['street'] ?? '')),
            'city' => trim((string) ($input['city'] ?? '')),
            'region' => trim((string) ($input['region'] ?? '')),
            'postal_code' => trim((string) ($input['postal_code'] ?? '')),
            'country' => trim((string) ($input['country'] ?? '')),
        ];
    }

    public static function importedEmployee(array $input): array
    {
        $errors = [];
        $requiredFields = [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'organization' => 'Organization',
            'title' => 'Title',
            'phone' => 'Phone',
            'email' => 'Email',
            'street' => 'Street',
            'city' => 'City',
            'region' => 'Region',
            'postal_code' => 'Postal code',
            'country' => 'Country',
        ];

        foreach ($requiredFields as $field => $label) {
            if (($input[$field] ?? '') === '') {
                $errors[$field] = $label . ' is required.';
            }
        }

        if (($input['first_name'] ?? '') !== '' && self::stringLength($input['first_name']) > 80) {
            $errors['first_name'] = 'First name must be 80 characters or less.';
        }

        if (($input['employee_number'] ?? null) !== null && self::stringLength($input['employee_number']) > 50) {
            $errors['employee_number'] = 'Employee number must be 50 characters or less.';
        }

        if (($input['last_name'] ?? '') !== '' && self::stringLength($input['last_name']) > 80) {
            $errors['last_name'] = 'Last name must be 80 characters or less.';
        }

        if (($input['organization'] ?? '') !== '' && self::stringLength($input['organization']) > 150) {
            $errors['organization'] = 'Organization must be 150 characters or less.';
        }

        if (($input['title'] ?? '') !== '' && self::stringLength($input['title']) > 150) {
            $errors['title'] = 'Title must be 150 characters or less.';
        }

        if (($input['phone'] ?? '') !== '' && !preg_match('/^[0-9+\-\s()]{7,50}$/', $input['phone'])) {
            $errors['phone'] = 'Phone number format looks invalid.';
        }

        if (($input['email'] ?? '') !== '' && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (($input['email'] ?? '') !== '' && self::stringLength($input['email']) > 150) {
            $errors['email'] = 'Email address must be 150 characters or less.';
        }

        if (($input['street'] ?? '') !== '' && self::stringLength($input['street']) > 150) {
            $errors['street'] = 'Street must be 150 characters or less.';
        }

        if (($input['city'] ?? '') !== '' && self::stringLength($input['city']) > 120) {
            $errors['city'] = 'City must be 120 characters or less.';
        }

        if (($input['region'] ?? '') !== '' && self::stringLength($input['region']) > 120) {
            $errors['region'] = 'Region must be 120 characters or less.';
        }

        if (($input['postal_code'] ?? '') !== '' && self::stringLength($input['postal_code']) > 30) {
            $errors['postal_code'] = 'Postal code must be 30 characters or less.';
        }

        if (($input['country'] ?? '') !== '' && self::stringLength($input['country']) > 120) {
            $errors['country'] = 'Country must be 120 characters or less.';
        }

        return $errors;
    }

    public static function flattenErrors(array $errors): string
    {
        return implode(' ', array_values($errors));
    }

    private static function nullableString(mixed $value): ?string
    {
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }

    private static function stringLength(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return \mb_strlen($value);
        }

        return strlen($value);
    }
}
