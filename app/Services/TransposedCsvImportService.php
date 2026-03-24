<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class TransposedCsvImportService
{
    public const FIELD_MAP = [
        'employeenumber' => ['label' => 'EmployeeNumber', 'key' => 'employee_number', 'required' => false],
        'honorific' => ['label' => 'Honorific', 'key' => 'honorific', 'required' => false],
        'suffix' => ['label' => 'Suffix', 'key' => 'suffix', 'required' => false],
        'lastname' => ['label' => 'LastName', 'key' => 'last_name'],
        'firstname' => ['label' => 'FirstName', 'key' => 'first_name'],
        'organization' => ['label' => 'Organization', 'key' => 'organization'],
        'title' => ['label' => 'Title', 'key' => 'title'],
        'phone' => ['label' => 'Phone', 'key' => 'phone'],
        'email' => ['label' => 'Email', 'key' => 'email'],
        'street' => ['label' => 'Street', 'key' => 'street'],
        'city' => ['label' => 'City', 'key' => 'city'],
        'region' => ['label' => 'Region', 'key' => 'region'],
        'postalcode' => ['label' => 'PostalCode', 'key' => 'postal_code'],
        'country' => ['label' => 'Country', 'key' => 'country'],
    ];

    public function parse(string $filePath): array
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new RuntimeException('The uploaded CSV file could not be read.');
        }

        $handle = fopen($filePath, 'rb');

        if ($handle === false) {
            throw new RuntimeException('Unable to open the uploaded CSV file.');
        }

        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null]) {
                continue;
            }

            $rows[] = array_map(static function ($value): string {
                return trim((string) $value);
            }, $row);
        }

        fclose($handle);

        if ($rows === []) {
            throw new RuntimeException('The CSV file is empty.');
        }

        $fieldRows = [];
        $maxColumns = 1;

        foreach ($rows as $row) {
            $rawLabel = $this->stripBom((string) ($row[0] ?? ''));

            if ($rawLabel === '') {
                continue;
            }

            $normalizedLabel = $this->normalizeLabel($rawLabel);

            if (!isset(self::FIELD_MAP[$normalizedLabel])) {
                continue;
            }

            $fieldRows[self::FIELD_MAP[$normalizedLabel]['key']] = $row;
            $maxColumns = max($maxColumns, count($row));
        }

        $missingRows = [];

        foreach (self::FIELD_MAP as $field) {
            if (($field['required'] ?? true) === false) {
                continue;
            }

            if (!isset($fieldRows[$field['key']])) {
                $missingRows[] = $field['label'];
            }
        }

        if ($missingRows !== []) {
            throw new RuntimeException(
                'The CSV is missing these required field rows: ' . implode(', ', $missingRows) . '.'
            );
        }

        $employees = [];
        $skippedEmptyColumns = 0;

        for ($columnIndex = 1; $columnIndex < $maxColumns; $columnIndex++) {
            $employee = [];
            $hasAnyValue = false;

            foreach (self::FIELD_MAP as $field) {
                $value = trim((string) ($fieldRows[$field['key']][$columnIndex] ?? ''));
                $employee[$field['key']] = $value;

                if ($value !== '') {
                    $hasAnyValue = true;
                }
            }

            if (!$hasAnyValue) {
                $skippedEmptyColumns++;
                continue;
            }

            $employees[] = [
                'column_index' => $columnIndex,
                'column_label' => $this->spreadsheetColumnLabel($columnIndex + 1),
                'data' => $employee,
            ];
        }

        if ($employees === []) {
            throw new RuntimeException('No employee columns with data were found in the CSV.');
        }

        return [
            'employees' => $employees,
            'skipped_empty_columns' => $skippedEmptyColumns,
        ];
    }

    private function normalizeLabel(string $value): string
    {
        $value = strtolower($value);

        return preg_replace('/[^a-z0-9]+/', '', $value) ?: '';
    }

    private function stripBom(string $value): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
    }

    private function spreadsheetColumnLabel(int $columnNumber): string
    {
        $label = '';

        while ($columnNumber > 0) {
            $columnNumber--;
            $label = chr(($columnNumber % 26) + 65) . $label;
            $columnNumber = intdiv($columnNumber, 26);
        }

        return $label;
    }
}
