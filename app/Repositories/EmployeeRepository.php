<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class EmployeeRepository
{
    private static function searchColumns(): array
    {
        return [
            'first_name',
            'last_name',
            'CONCAT(first_name, " ", last_name)',
            'position',
            'COALESCE(department, "")',
            'phone',
            'email',
        ];
    }

    private static function searchTerms(string $search): array
    {
        $terms = preg_split('/\s+/', trim($search)) ?: [];
        $terms = array_values(array_filter(array_map(static fn (string $term): string => trim($term), $terms)));

        return array_slice($terms, 0, 5);
    }

    private static function searchCondition(array $terms): string
    {
        $groups = [];

        foreach ($terms as $termIndex => $_term) {
            $clauses = [];

            foreach (self::searchColumns() as $columnIndex => $column) {
                $clauses[] = $column . ' LIKE :search_' . $termIndex . '_' . $columnIndex;
            }

            $groups[] = '(' . implode(' OR ', $clauses) . ')';
        }

        return implode(' AND ', $groups);
    }

    private static function bindSearchValues(\PDOStatement $statement, array $terms): void
    {
        foreach ($terms as $termIndex => $term) {
            foreach (self::searchColumns() as $columnIndex => $_column) {
                $statement->bindValue(':search_' . $termIndex . '_' . $columnIndex, '%' . $term . '%');
            }
        }
    }

    public static function all(PDO $pdo): array
    {
        $statement = $pdo->query('
            SELECT *
            FROM employees
            ORDER BY status ASC, last_name ASC, first_name ASC
        ');

        return $statement->fetchAll();
    }

    public static function paginate(PDO $pdo, int $limit, int $offset, string $search = ''): array
    {
        $terms = self::searchTerms($search);
        $sql = '
            SELECT *
            FROM employees
        ';

        if ($terms !== []) {
            $sql .= ' WHERE ' . self::searchCondition($terms);
        }

        $sql .= '
            ORDER BY status ASC, last_name ASC, first_name ASC
            LIMIT :limit OFFSET :offset
        ';

        $statement = $pdo->prepare($sql);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);

        if ($terms !== []) {
            self::bindSearchValues($statement, $terms);
        }

        $statement->execute();

        return $statement->fetchAll();
    }

    public static function countFiltered(PDO $pdo, string $search = ''): int
    {
        $terms = self::searchTerms($search);
        $sql = 'SELECT COUNT(*) FROM employees';

        if ($terms !== []) {
            $sql .= ' WHERE ' . self::searchCondition($terms);
        }

        $statement = $pdo->prepare($sql);

        if ($terms !== []) {
            self::bindSearchValues($statement, $terms);
        }

        $statement->execute();

        return (int) $statement->fetchColumn();
    }

    public static function recent(PDO $pdo, int $limit = 5): array
    {
        $statement = $pdo->prepare('
            SELECT *
            FROM employees
            ORDER BY created_at DESC
            LIMIT :limit
        ');
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public static function countAll(PDO $pdo): int
    {
        return (int) $pdo->query('SELECT COUNT(*) FROM employees')->fetchColumn();
    }

    public static function countByStatus(PDO $pdo, string $status): int
    {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE status = :status');
        $statement->execute(['status' => $status]);

        return (int) $statement->fetchColumn();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $statement = $pdo->prepare('SELECT * FROM employees WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public static function findByToken(PDO $pdo, string $token): ?array
    {
        $statement = $pdo->prepare('SELECT * FROM employees WHERE public_token = :token LIMIT 1');
        $statement->execute(['token' => $token]);

        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public static function tokenExists(PDO $pdo, string $token): bool
    {
        $statement = $pdo->prepare('SELECT COUNT(*) FROM employees WHERE public_token = :token');
        $statement->execute(['token' => $token]);

        return (int) $statement->fetchColumn() > 0;
    }

    public static function create(PDO $pdo, array $data): int
    {
        $statement = $pdo->prepare('
            INSERT INTO employees (
                public_token,
                first_name,
                last_name,
                position,
                department,
                phone,
                email,
                location,
                qr_code_path,
                status
            ) VALUES (
                :public_token,
                :first_name,
                :last_name,
                :position,
                :department,
                :phone,
                :email,
                :location,
                :qr_code_path,
                :status
            )
        ');

        $statement->execute([
            'public_token' => $data['public_token'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'position' => $data['position'],
            'department' => $data['department'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'location' => $data['location'],
            'qr_code_path' => $data['qr_code_path'],
            'status' => $data['status'],
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function update(PDO $pdo, int $id, array $data): void
    {
        $statement = $pdo->prepare('
            UPDATE employees
            SET
                first_name = :first_name,
                last_name = :last_name,
                position = :position,
                department = :department,
                phone = :phone,
                email = :email,
                location = :location
            WHERE id = :id
        ');

        $statement->execute([
            'id' => $id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'position' => $data['position'],
            'department' => $data['department'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'location' => $data['location'],
        ]);
    }

    public static function deactivate(PDO $pdo, int $id): void
    {
        $statement = $pdo->prepare("
            UPDATE employees
            SET status = 'inactive'
            WHERE id = :id
        ");

        $statement->execute(['id' => $id]);
    }

    public static function activate(PDO $pdo, int $id): void
    {
        $statement = $pdo->prepare("
            UPDATE employees
            SET status = 'active'
            WHERE id = :id
        ");

        $statement->execute(['id' => $id]);
    }

    public static function updateQrCodePath(PDO $pdo, int $id, ?string $qrCodePath): void
    {
        $statement = $pdo->prepare('
            UPDATE employees
            SET qr_code_path = :qr_code_path
            WHERE id = :id
        ');

        $statement->execute([
            'id' => $id,
            'qr_code_path' => $qrCodePath,
        ]);
    }
}
