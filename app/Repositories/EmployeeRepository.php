<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

class EmployeeRepository
{
    private static array $columnCache = [];

    private const BULK_SCHEMA_COLUMNS = [
        'organization',
        'title',
        'street',
        'city',
        'region',
        'postal_code',
        'country',
        'mecard_payload',
        'qr_code_path',
        'updated_at',
    ];

    public static function missingBulkSchemaColumns(PDO $pdo): array
    {
        $columns = self::columnNames($pdo);

        return array_values(array_diff(self::BULK_SCHEMA_COLUMNS, $columns));
    }

    public static function supportsEmployeeNumber(PDO $pdo): bool
    {
        return in_array('employee_number', self::columnNames($pdo), true);
    }

    public static function supportsHonorific(PDO $pdo): bool
    {
        return in_array('honorific', self::columnNames($pdo), true);
    }

    public static function supportsSuffix(PDO $pdo): bool
    {
        return in_array('suffix', self::columnNames($pdo), true);
    }

    private static function searchColumns(): array
    {
        return [
            'first_name',
            'last_name',
            'CONCAT(first_name, " ", last_name)',
            'organization',
            'title',
            'phone',
            'email',
            'city',
            'country',
        ];
    }

    private static function searchTerms(string $search): array
    {
        $terms = preg_split('/\s+/', trim($search)) ?: [];
        $terms = array_values(array_filter(array_map(static fn (string $term): string => trim($term), $terms)));

        return array_slice($terms, 0, 6);
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
            ORDER BY updated_at DESC, last_name ASC, first_name ASC
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

    public static function all(PDO $pdo): array
    {
        $statement = $pdo->query('
            SELECT *
            FROM employees
            ORDER BY updated_at DESC, last_name ASC, first_name ASC
        ');

        return $statement->fetchAll();
    }

    public static function allForZip(PDO $pdo): array
    {
        $statement = $pdo->query('
            SELECT *
            FROM employees
            ORDER BY last_name ASC, first_name ASC
        ');

        return $statement->fetchAll();
    }

    public static function recent(PDO $pdo, int $limit = 5): array
    {
        $statement = $pdo->prepare('
            SELECT *
            FROM employees
            ORDER BY updated_at DESC
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

    public static function countWithQr(PDO $pdo): int
    {
        return (int) $pdo->query('SELECT COUNT(*) FROM employees WHERE qr_code_path IS NOT NULL')->fetchColumn();
    }

    public static function countDistinctOrganizations(PDO $pdo): int
    {
        return (int) $pdo->query('SELECT COUNT(DISTINCT organization) FROM employees')->fetchColumn();
    }

    public static function find(PDO $pdo, int $id): ?array
    {
        $statement = $pdo->prepare('SELECT * FROM employees WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public static function findByEmail(PDO $pdo, string $email): ?array
    {
        $statement = $pdo->prepare('SELECT * FROM employees WHERE email = :email LIMIT 1');
        $statement->execute(['email' => $email]);

        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public static function findByPhone(PDO $pdo, string $phone): ?array
    {
        $statement = $pdo->prepare('SELECT * FROM employees WHERE phone = :phone LIMIT 1');
        $statement->execute(['phone' => $phone]);

        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public static function findByEmployeeNumber(PDO $pdo, string $employeeNumber): ?array
    {
        if (!self::supportsEmployeeNumber($pdo)) {
            return null;
        }

        $statement = $pdo->prepare('SELECT * FROM employees WHERE employee_number = :employee_number LIMIT 1');
        $statement->execute(['employee_number' => $employeeNumber]);

        $employee = $statement->fetch();

        return $employee ?: null;
    }

    public static function upsertContact(PDO $pdo, array $data): array
    {
        $duplicateFields = [];

        if (($data['email'] ?? '') !== '' && self::findByEmail($pdo, $data['email'])) {
            $duplicateFields[] = 'email';
        }

        if (($data['phone'] ?? '') !== '' && self::findByPhone($pdo, $data['phone'])) {
            $duplicateFields[] = 'phone number';
        }

        if (($data['employee_number'] ?? null) !== null
            && self::findByEmployeeNumber($pdo, (string) $data['employee_number'])) {
            $duplicateFields[] = 'employee number';
        }

        if ($duplicateFields !== []) {
            throw new RuntimeException(
                'A contact with the same ' . implode(', ', $duplicateFields)
                . ' already exists. Duplicate records are blocked to avoid accidental replacement.'
            );
        }

        return [
            'id' => self::create($pdo, $data),
            'action' => 'created',
        ];
    }

    public static function updateGeneratedAssets(PDO $pdo, int $id, string $contactPayload, string $qrCodePath): void
    {
        $statement = $pdo->prepare('
            UPDATE employees
            SET
                mecard_payload = :contact_payload,
                qr_code_path = :qr_code_path
            WHERE id = :id
        ');

        $statement->execute([
            'id' => $id,
            'contact_payload' => $contactPayload,
            'qr_code_path' => $qrCodePath,
        ]);
    }

    private static function create(PDO $pdo, array $data): int
    {
        $columns = [
            'first_name',
            'last_name',
            'organization',
            'title',
            'phone',
            'email',
            'street',
            'city',
            'region',
            'postal_code',
            'country',
        ];

        if (self::supportsSuffix($pdo)) {
            array_splice($columns, 2, 0, 'suffix');
        }

        if (self::supportsHonorific($pdo)) {
            array_splice($columns, 1, 0, 'honorific');
        }

        if (self::supportsEmployeeNumber($pdo)) {
            array_unshift($columns, 'employee_number');
        }

        $placeholders = array_map(static fn (string $column): string => ':' . $column, $columns);

        $statement = $pdo->prepare(
            'INSERT INTO employees (' . implode(', ', $columns) . ', mecard_payload, qr_code_path) VALUES ('
            . implode(', ', $placeholders) . ', NULL, NULL)'
        );

        $statement->execute(self::contactParams($pdo, $data));

        return (int) $pdo->lastInsertId();
    }

    private static function updateContact(PDO $pdo, int $id, array $data): void
    {
        $assignments = [
            'first_name = :first_name',
            'last_name = :last_name',
            'organization = :organization',
            'title = :title',
            'phone = :phone',
            'email = :email',
            'street = :street',
            'city = :city',
            'region = :region',
            'postal_code = :postal_code',
            'country = :country',
        ];

        if (self::supportsSuffix($pdo)) {
            array_splice($assignments, 2, 0, 'suffix = :suffix');
        }

        if (self::supportsHonorific($pdo)) {
            array_splice($assignments, 1, 0, 'honorific = :honorific');
        }

        if (self::supportsEmployeeNumber($pdo)) {
            array_unshift($assignments, 'employee_number = :employee_number');
        }

        $statement = $pdo->prepare(
            'UPDATE employees SET ' . implode(', ', $assignments) . ' WHERE id = :id'
        );

        $params = self::contactParams($pdo, $data);
        $params['id'] = $id;
        $statement->execute($params);
    }

    private static function contactParams(PDO $pdo, array $data): array
    {
        $params = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'organization' => $data['organization'],
            'title' => $data['title'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'street' => $data['street'],
            'city' => $data['city'],
            'region' => $data['region'],
            'postal_code' => $data['postal_code'],
            'country' => $data['country'],
        ];

        if (self::supportsSuffix($pdo)) {
            $params['suffix'] = $data['suffix'] ?? null;
        }

        if (self::supportsHonorific($pdo)) {
            $params['honorific'] = $data['honorific'] ?? null;
        }

        if (self::supportsEmployeeNumber($pdo)) {
            $params['employee_number'] = $data['employee_number'] ?? null;
        }

        return $params;
    }

    private static function columnNames(PDO $pdo): array
    {
        $cacheKey = spl_object_id($pdo);

        if (isset(self::$columnCache[$cacheKey])) {
            return self::$columnCache[$cacheKey];
        }

        $statement = $pdo->query('SHOW COLUMNS FROM employees');
        $columns = array_map(
            static fn (array $column): string => (string) ($column['Field'] ?? ''),
            $statement->fetchAll()
        );

        self::$columnCache[$cacheKey] = $columns;

        return $columns;
    }
}
