<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\Value;
use PDO;
use PDOStatement;
use RuntimeException;

final class ActivityLogRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{user_id: int|null, action: string, entity: string, entity_id: int|null, description: string, ip_address: string} $data
     */
    public function create(array $data): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO activity_logs (user_id, action, entity, entity_id, description, ip_address)
             VALUES (:user_id, :action, :entity, :entity_id, :description, :ip_address)',
        );

        $statement->execute($data);
    }

    /**
     * @return array{items: list<array<string, mixed>>, total: int, page: int, pages: int}
     */
    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $total = Value::toInt($this->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn());

        $perPage = max(1, $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT a.*, u.full_name AS user_name
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT :limit OFFSET :offset',
        );
        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return [
            'items' => $this->fetchRows($statement),
            'total' => $total,
            'page'  => $page,
            'pages' => $pages,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forEntity(string $entity, int $entityId, int $limit = 8): array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.*, u.full_name AS user_name
             FROM activity_logs a
             LEFT JOIN users u ON u.id = a.user_id
             WHERE a.entity = :entity AND a.entity_id = :entity_id
             ORDER BY a.created_at DESC, a.id DESC
             LIMIT :limit',
        );
        $statement->bindValue(':entity', $entity, PDO::PARAM_STR);
        $statement->bindValue(':entity_id', $entityId, PDO::PARAM_INT);
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        return $this->fetchRows($statement);
    }

    /**
     * @return array{total: int, today: int, students_created: int}
     */
    public function stats(): array
    {
        // Compute "today" in PHP so the query is portable across MySQL and
        // PostgreSQL (DATE()/CURDATE() are MySQL-only functions).
        $today = new \DateTimeImmutable('today');
        $tomorrow = $today->modify('+1 day');

        $statement = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN created_at >= :today AND created_at < :tomorrow THEN 1 ELSE 0 END), 0) AS today,
                COALESCE(SUM(CASE WHEN action = 'create_student' THEN 1 ELSE 0 END), 0) AS students_created
             FROM activity_logs",
        );
        $statement->execute([
            'today'    => $today->format('Y-m-d 00:00:00'),
            'tomorrow' => $tomorrow->format('Y-m-d 00:00:00'),
        ]);

        $row = $statement->fetch();
        $row = is_array($row) ? $row : [];

        return [
            'total' => Value::int($row, 'total'),
            'today' => Value::int($row, 'today'),
            'students_created' => Value::int($row, 'students_created'),
        ];
    }

    private function query(string $sql): PDOStatement
    {
        $statement = $this->pdo->query($sql);

        if (!$statement instanceof PDOStatement) {
            throw new RuntimeException('Unable to run the requested activity query.');
        }

        return $statement;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchRows(PDOStatement $statement): array
    {
        $rows = [];

        foreach ($statement->fetchAll() as $row) {
            if (!is_array($row)) {
                continue;
            }

            $normalised = [];

            foreach ($row as $key => $value) {
                if (is_string($key)) {
                    $normalised[$key] = $value;
                }
            }

            $rows[] = $normalised;
        }

        return $rows;
    }
}
