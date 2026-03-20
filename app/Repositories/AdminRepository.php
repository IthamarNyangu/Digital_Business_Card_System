<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class AdminRepository
{
    public static function findByUsername(PDO $pdo, string $username): ?array
    {
        $statement = $pdo->prepare('SELECT * FROM admins WHERE username = :username LIMIT 1');
        $statement->execute(['username' => $username]);

        $admin = $statement->fetch();

        return $admin ?: null;
    }

    public static function create(PDO $pdo, string $username, string $passwordHash): int
    {
        $statement = $pdo->prepare('
            INSERT INTO admins (username, password_hash)
            VALUES (:username, :password_hash)
        ');

        $statement->execute([
            'username' => $username,
            'password_hash' => $passwordHash,
        ]);

        return (int) $pdo->lastInsertId();
    }
}
