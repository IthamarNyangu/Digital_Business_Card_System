<?php

declare(strict_types=1);

namespace App\Services;

class MecardService
{
    public function build(array $employee): string
    {
        $name = $this->escapeValue(trim(((string) ($employee['last_name'] ?? '')) . ',' . ((string) ($employee['first_name'] ?? ''))));
        $organization = $this->escapeValue((string) ($employee['organization'] ?? config('app.organisation_name', '')));
        $title = $this->escapeValue((string) ($employee['title'] ?? $employee['position'] ?? ''));
        $phone = $this->escapeValue((string) ($employee['phone'] ?? ''));
        $email = $this->escapeValue((string) ($employee['email'] ?? ''));
        $address = $this->escapeValue(implode(',', [
            (string) ($employee['street'] ?? $employee['location'] ?? ''),
            (string) ($employee['city'] ?? ''),
            (string) ($employee['region'] ?? ''),
            (string) ($employee['postal_code'] ?? ''),
            (string) ($employee['country'] ?? ''),
        ]));
        $note = $this->escapeValue(trim($title . ', ' . $organization, ' ,'));

        return 'MECARD:'
            . 'N:' . $name . ';'
            . 'ORG:' . $organization . ';'
            . 'TITLE:' . $title . ';'
            . 'TEL:' . $phone . ';'
            . 'EMAIL:' . $email . ';'
            . 'ADR:' . $address . ';'
            . 'NOTE:' . $note . ';'
            . ';';
    }

    private function escapeValue(string $value): string
    {
        $value = str_replace(["\r\n", "\n", "\r"], ' ', $value);

        return str_replace(
            ['\\', ';', ':', ','],
            ['\\\\', '\;', '\:', '\,'],
            $value
        );
    }
}
