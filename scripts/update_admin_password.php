<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$options = getopt('', ['username:', 'password:']);
$username = trim((string) ($options['username'] ?? ''));
$password = (string) ($options['password'] ?? '');

if ($username === '' || $password === '') {
    fwrite(STDERR, "Usage: php scripts/update_admin_password.php --username=admin --password=NewPassword123!\n");
    exit(1);
}

$statement = db()->prepare('
    UPDATE admins
    SET password_hash = :password_hash
    WHERE username = :username
');

$statement->execute([
    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    'username' => $username,
]);

if ($statement->rowCount() < 1) {
    fwrite(STDERR, "No admin account found for username: {$username}\n");
    exit(1);
}

fwrite(STDOUT, "Password updated successfully for admin: {$username}\n");
