<?php

declare(strict_types=1);

namespace App\Services;

class VCardService
{
    public function build(array $employee): string
    {
        $fullName = trim($employee['first_name'] . ' ' . $employee['last_name']);
        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'N:' . $this->escape($employee['last_name']) . ';' . $this->escape($employee['first_name']) . ';;;',
            'FN:' . $this->escape($fullName),
            'ORG:' . $this->escape((string) config('app.organisation_name')),
            'TITLE:' . $this->escape($employee['position']),
            'TEL;TYPE=WORK,VOICE:' . $this->escape($employee['phone']),
            'EMAIL;TYPE=WORK,INTERNET:' . $this->escape($employee['email']),
        ];

        if (!empty($employee['location'])) {
            $lines[] = 'ADR;TYPE=WORK:;;' . $this->escape($employee['location']) . ';;;;';
        }

        if (!empty($employee['department'])) {
            $lines[] = 'NOTE:' . $this->escape('Department: ' . $employee['department']);
        }

        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines) . "\r\n";
    }

    private function escape(?string $value): string
    {
        // vCard values need a little escaping so commas, semicolons, and line breaks remain valid.
        $clean = str_replace(["\r\n", "\r", "\n"], '\\n', (string) $value);
        $clean = str_replace(['\\', ';', ',', ':'], ['\\\\', '\;', '\,', '\:'], $clean);

        return $clean;
    }
}
