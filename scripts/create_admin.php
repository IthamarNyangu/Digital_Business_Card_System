<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Repositories\AdminRepository;

if (PHP_SAPI !== 'cli') {
    exit("This script can only be run from the command line.\n");
}

function prompt(string $label): string
{
    echo $label;
    $value = fgets(STDIN);

    return trim((string) $value);
}

$options = getopt('', ['username:', 'password:']);

$username = trim((string) ($options['username'] ?? prompt('Admin username: ')));
$password = (string) ($options['password'] ?? prompt('Admin password: '));

if ($username === '' || $password === '') {
    exit("Username and password are required.\n");
}

if (AdminRepository::findByUsername(db(), $username)) {
    exit("An admin with that username already exists.\n");
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);
AdminRepository::create(db(), $username, $passwordHash);

echo "Admin created successfully.\n";
