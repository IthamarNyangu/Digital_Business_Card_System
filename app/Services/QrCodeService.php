<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class QrCodeService
{
    public function generateForEmployee(array $employee): string
    {
        $relativePath = 'storage/qrcodes/employee-' . $employee['id'] . '-' . $employee['public_token'] . '.png';
        $publicUrl = url('/c/' . $employee['public_token']);

        // The QR code stores the public contact URL, not any internal employee identifier.
        $this->generatePng($publicUrl, $relativePath);

        return $relativePath;
    }

    public function generatePng(string $content, string $relativePath): void
    {
        if (!class_exists(\chillerlan\QRCode\QRCode::class)) {
            throw new RuntimeException('QR library is missing. Run composer install first.');
        }

        if (!extension_loaded('gd')) {
            throw new RuntimeException('The PHP GD extension is required to create PNG QR codes.');
        }

        $absolutePath = base_path($relativePath);
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

        // Passing a file path as the second argument writes the PNG directly to disk.
        (new \chillerlan\QRCode\QRCode($options))->render($content, $absolutePath);
    }
}
