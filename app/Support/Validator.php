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

    public static function employeePayload(array $input): array
    {
        return [
            'first_name' => trim((string) ($input['first_name'] ?? '')),
            'last_name' => trim((string) ($input['last_name'] ?? '')),
            'position' => trim((string) ($input['position'] ?? '')),
            'department' => self::nullableString($input['department'] ?? null),
            'phone' => trim((string) ($input['phone'] ?? '')),
            'email' => trim((string) ($input['email'] ?? '')),
            'location' => self::nullableString($input['location'] ?? null),
        ];
    }

    public static function employee(array $input): array
    {
        $errors = [];

        if (($input['first_name'] ?? '') === '') {
            $errors['first_name'] = 'First name is required.';
        } elseif (mb_strlen($input['first_name']) > 80) {
            $errors['first_name'] = 'First name must be 80 characters or less.';
        }

        if (($input['last_name'] ?? '') === '') {
            $errors['last_name'] = 'Last name is required.';
        } elseif (mb_strlen($input['last_name']) > 80) {
            $errors['last_name'] = 'Last name must be 80 characters or less.';
        }

        if (($input['position'] ?? '') === '') {
            $errors['position'] = 'Position is required.';
        } elseif (mb_strlen($input['position']) > 120) {
            $errors['position'] = 'Position must be 120 characters or less.';
        }

        if (($input['department'] ?? null) !== null && mb_strlen($input['department']) > 120) {
            $errors['department'] = 'Department must be 120 characters or less.';
        }

        if (($input['phone'] ?? '') === '') {
            $errors['phone'] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9+\-\s()]{7,50}$/', $input['phone'])) {
            $errors['phone'] = 'Phone number format looks invalid.';
        }

        if (($input['email'] ?? '') === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (mb_strlen($input['email']) > 150) {
            $errors['email'] = 'Email address must be 150 characters or less.';
        }

        if (($input['location'] ?? null) !== null && mb_strlen($input['location']) > 150) {
            $errors['location'] = 'Location must be 150 characters or less.';
        }

        return $errors;
    }

    private static function nullableString(mixed $value): ?string
    {
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }
}
