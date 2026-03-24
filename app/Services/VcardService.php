<?php

declare(strict_types=1);

namespace App\Services;

class VcardService
{
    public function build(array $employee): string
    {
        $honorific = trim((string) ($employee['honorific'] ?? ''));
        $suffix = trim((string) ($employee['suffix'] ?? ''));
        $firstName = trim((string) ($employee['first_name'] ?? ''));
        $lastName = trim((string) ($employee['last_name'] ?? ''));
        $fullName = trim(implode(' ', array_filter([$honorific, $firstName, $lastName, $suffix])));
        $organization = trim((string) ($employee['organization'] ?? config('app.organisation_name', '')));
        $title = trim((string) ($employee['title'] ?? $employee['position'] ?? ''));
        $phone = trim((string) ($employee['phone'] ?? ''));
        $email = trim((string) ($employee['email'] ?? ''));
        $street = trim((string) ($employee['street'] ?? $employee['location'] ?? ''));
        $city = trim((string) ($employee['city'] ?? ''));
        $region = trim((string) ($employee['region'] ?? ''));
        $postalCode = trim((string) ($employee['postal_code'] ?? ''));
        $country = trim((string) ($employee['country'] ?? ''));
        $website = trim((string) config('app.organisation_website', ''));

        $lines = [
            'BEGIN:VCARD',
            'VERSION:3.0',
            'FN:' . $this->escapeText($fullName !== '' ? $fullName : trim($firstName . ' ' . $lastName)),
            'N:' . $this->escapeText($lastName) . ';'
                . $this->escapeText($firstName) . ';;'
                . $this->escapeText($honorific) . ';'
                . $this->escapeText($suffix),
            'ORG:' . $this->escapeText($organization),
        ];

        if ($title !== '') {
            $lines[] = 'TITLE:' . $this->escapeText($title);
        }

        if ($phone !== '') {
            $lines[] = 'TEL;TYPE=WORK,CELL,VOICE:' . $this->escapeText($phone);
        }

        if ($email !== '') {
            $lines[] = 'EMAIL;TYPE=WORK,INTERNET:' . $this->escapeText($email);
        }

        if ($street !== '' || $city !== '' || $region !== '' || $postalCode !== '' || $country !== '') {
            $lines[] = 'ADR;TYPE=WORK:;;'
                . $this->escapeText($street) . ';'
                . $this->escapeText($city) . ';'
                . $this->escapeText($region) . ';'
                . $this->escapeText($postalCode) . ';'
                . $this->escapeText($country);
        }

        if ($website !== '') {
            $lines[] = 'URL;TYPE=WORK:' . $this->escapeText($website);
        }

        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines);
    }

    private function escapeText(string $value): string
    {
        $value = str_replace(["\r\n", "\n", "\r"], '\n', $value);

        return str_replace(
            ['\\', ';', ','],
            ['\\\\', '\;', '\,'],
            $value
        );
    }
}
