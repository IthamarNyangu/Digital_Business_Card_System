<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class QrCodeService
{
    public function generateForEmployee(array $employee): string
    {
        $mecardPayload = (string) ($employee['mecard_payload'] ?? '');

        if ($mecardPayload === '') {
            throw new RuntimeException('The MECARD payload is missing, so the QR code cannot be generated.');
        }

        $relativePath = 'storage/qrcodes/' . $this->buildFilename($employee);
        $absolutePath = base_path($relativePath);
        $previousPath = (string) ($employee['qr_code_path'] ?? '');

        if ($previousPath !== '' && $previousPath !== $relativePath) {
            $oldAbsolutePath = base_path($previousPath);

            if (is_file($oldAbsolutePath)) {
                @unlink($oldAbsolutePath);
            }
        }

        $this->generatePng($mecardPayload, $absolutePath);

        return $relativePath;
    }

    public function generatePng(string $content, string $absolutePath): void
    {
        if (!class_exists(\chillerlan\QRCode\QRCode::class)) {
            throw new RuntimeException('QR library is missing. Run composer install first.');
        }

        if (!extension_loaded('gd')) {
            throw new RuntimeException('The PHP GD extension is required to create PNG QR codes.');
        }

        $directory = dirname($absolutePath);

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create QR code storage directory.');
        }

        $options = new \chillerlan\QRCode\QROptions([
            'outputType' => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,
            'outputBase64' => false,
            'scale' => 8,
            'addQuietzone' => true,
            'imageTransparent' => false,
        ]);

        (new \chillerlan\QRCode\QRCode($options))->render($content, $absolutePath);
    }

    private function buildFilename(array $employee): string
    {
        $firstName = $this->slugPart((string) ($employee['first_name'] ?? 'employee'));
        $lastName = $this->slugPart((string) ($employee['last_name'] ?? 'contact'));
        $id = (int) ($employee['id'] ?? 0);

        return $firstName . '_' . $lastName . '_' . $id . '_qrcode.png';
    }

    private function slugPart(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', '_', $value) ?? '';
        $value = trim($value, '_');

        return $value !== '' ? $value : 'contact';
    }
}
