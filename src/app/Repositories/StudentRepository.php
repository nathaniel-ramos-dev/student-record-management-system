<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Student;
use App\Support\Value;
use PDO;
use PDOStatement;
use RuntimeException;

/**
 * All student database access lives here. Every query is parameterised.
 */
final class StudentRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{search?: string, course?: string, year_level?: string|int, status?: string} $filters
     * @return array{items: list<Student>, total: int, page: int, perPage: int, pages: int}
     */
    public function paginate(array $filters, int $page = 1, int $perPage = 10): array
    {
        [$whereSql, $parameters] = $this->buildWhere($filters);

        $countStatement = $this->pdo->prepare('SELECT COUNT(*) FROM students ' . $whereSql);
        $countStatement->execute($parameters);
        $total = Value::toInt($countStatement->fetchColumn());

        $perPage = max(1, $perPage);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $offset = ($page - 1) * $perPage;

        $statement = $this->pdo->prepare(
            'SELECT * FROM students ' . $whereSql . '
             ORDER BY last_name ASC, first_name ASC
             LIMIT :limit OFFSET :offset',
        );

        foreach ($parameters as $key => $value) {
            $statement->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $items = [];

        foreach ($this->fetchRows($statement) as $row) {
            $items[] = Student::fromRow($row);
        }

        return [
            'items'   => $items,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => $pages,
        ];
    }

    /**
     * @param array{search?: string, course?: string, year_level?: string|int, status?: string} $filters
     * @return list<Student>
     */
    public function forExport(array $filters): array
    {
        [$whereSql, $parameters] = $this->buildWhere($filters);

        $statement = $this->pdo->prepare(
            'SELECT * FROM students ' . $whereSql . ' ORDER BY last_name ASC, first_name ASC',
        );
        $statement->execute($parameters);

        $students = [];

        foreach ($this->fetchRows($statement) as $row) {
            $students[] = Student::fromRow($row);
        }

        return $students;
    }

    public function find(int $id): ?Student
    {
        $statement = $this->pdo->prepare('SELECT * FROM students WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? Student::fromRow($this->normaliseRow($row)) : null;
    }

    public function studentNoExists(string $studentNo, ?int $exceptId = null): bool
    {
        return $this->exists('student_no', $studentNo, $exceptId);
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        return $this->exists('email', $email, $exceptId);
    }

    public function create(Student $student): int
    {
        $sql = 'INSERT INTO students
                (student_no, first_name, middle_name, last_name, email, phone,
                 gender, birth_date, course, year_level, address, status)
             VALUES
                (:student_no, :first_name, :middle_name, :last_name, :email, :phone,
                 :gender, :birth_date, :course, :year_level, :address, :status)';

        // PostgreSQL needs RETURNING to expose the generated key reliably.
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            $statement = $this->pdo->prepare($sql . ' RETURNING id');
            $statement->execute($student->toDatabase());

            return Value::toInt($statement->fetchColumn());
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($student->toDatabase());

        return Value::toInt($this->pdo->lastInsertId());
    }

    public function update(int $id, Student $student): void
    {
        $data = $student->toDatabase();
        $data['id'] = $id;

        $statement = $this->pdo->prepare(
            'UPDATE students SET
                student_no = :student_no,
                first_name = :first_name,
                middle_name = :middle_name,
                last_name  = :last_name,
                email      = :email,
                phone      = :phone,
                gender     = :gender,
                birth_date = :birth_date,
                course     = :course,
                year_level = :year_level,
                address    = :address,
                status     = :status
             WHERE id = :id',
        );

        $statement->execute($data);
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM students WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    /**
     * @return array{total: int, active: int, inactive: int, graduated: int, male: int, female: int, new_this_month: int}
     */
    public function stats(): array
    {
        // The month boundary is calculated in PHP so the query works on both
        // MySQL and PostgreSQL (DATE_FORMAT/CURDATE are MySQL-only).
        $monthStart = (new \DateTimeImmutable('first day of this month'))->format('Y-m-d 00:00:00');

        $statement = $this->pdo->prepare(
            "SELECT
                COUNT(*) AS total,
                COALESCE(SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END), 0) AS active,
                COALESCE(SUM(CASE WHEN status = 'Inactive' THEN 1 ELSE 0 END), 0) AS inactive,
                COALESCE(SUM(CASE WHEN status = 'Graduated' THEN 1 ELSE 0 END), 0) AS graduated,
                COALESCE(SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END), 0) AS male,
                COALESCE(SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END), 0) AS female,
                COALESCE(SUM(CASE WHEN created_at >= :month_start THEN 1 ELSE 0 END), 0) AS new_this_month
             FROM students",
        );
        $statement->execute(['month_start' => $monthStart]);

        $row = self::firstRow($statement);

        return [
            'total' => Value::int($row, 'total'),
            'active' => Value::int($row, 'active'),
            'inactive' => Value::int($row, 'inactive'),
            'graduated' => Value::int($row, 'graduated'),
            'male' => Value::int($row, 'male'),
            'female' => Value::int($row, 'female'),
            'new_this_month' => Value::int($row, 'new_this_month'),
        ];
    }

    /**
     * @return list<array{course: string, total: int}>
     */
    public function countByCourse(): array
    {
        $statement = $this->query(
            'SELECT course, COUNT(*) AS total
             FROM students
             GROUP BY course
             ORDER BY total DESC, course ASC',
        );

        $result = [];

        foreach ($this->fetchRows($statement) as $row) {
            $result[] = [
                'course' => Value::string($row, 'course'),
                'total'  => Value::int($row, 'total'),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, int>
     */
    public function countByYearLevel(): array
    {
        $statement = $this->query(
            'SELECT year_level, COUNT(*) AS total
             FROM students
             GROUP BY year_level
             ORDER BY year_level ASC',
        );

        $result = [];

        foreach ($this->fetchRows($statement) as $row) {
            $result[Value::int($row, 'year_level')] = Value::int($row, 'total');
        }

        return $result;
    }

    /**
     * @return list<Student>
     */
    public function recent(int $limit = 5): array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM students ORDER BY created_at DESC, id DESC LIMIT :limit',
        );
        $statement->bindValue(':limit', max(1, $limit), PDO::PARAM_INT);
        $statement->execute();

        $students = [];

        foreach ($this->fetchRows($statement) as $row) {
            $students[] = Student::fromRow($row);
        }

        return $students;
    }

    /**
     * @return list<string>
     */
    public function distinctCourses(): array
    {
        $statement = $this->query(
            "SELECT DISTINCT course FROM students WHERE course <> '' ORDER BY course ASC",
        );

        $courses = [];

        foreach ($statement->fetchAll(PDO::FETCH_COLUMN) as $course) {
            $course = Value::toString($course);

            if ($course !== '') {
                $courses[] = $course;
            }
        }

        return $courses;
    }

    private function exists(string $column, string $value, ?int $exceptId): bool
    {
        $sql = sprintf('SELECT COUNT(*) FROM students WHERE %s = :value', $column);
        $parameters = ['value' => $value];

        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $parameters['id'] = $exceptId;
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute($parameters);

        return Value::toInt($statement->fetchColumn()) > 0;
    }

    /**
     * @param array{search?: string, course?: string, year_level?: string|int, status?: string} $filters
     * @return array{0: string, 1: array<string, string>}
     */
    private function buildWhere(array $filters): array
    {
        $clauses = [];
        $parameters = [];

        $search = Value::string($filters, 'search');

        if ($search !== '') {
            $term = '%' . addcslashes($search, '%_\\') . '%';
            $clauses[] = '(student_no LIKE :search_no OR first_name LIKE :search_first
                           OR middle_name LIKE :search_middle OR last_name LIKE :search_last
                           OR email LIKE :search_email)';
            $parameters['search_no'] = $term;
            $parameters['search_first'] = $term;
            $parameters['search_middle'] = $term;
            $parameters['search_last'] = $term;
            $parameters['search_email'] = $term;
        }

        $course = Value::string($filters, 'course');

        if ($course !== '') {
            $clauses[] = 'course = :course';
            $parameters['course'] = $course;
        }

        $yearLevel = Value::int($filters, 'year_level');

        if ($yearLevel >= 1 && $yearLevel <= 5) {
            $clauses[] = 'year_level = :year_level';
            $parameters['year_level'] = (string) $yearLevel;
        }

        $status = Value::string($filters, 'status');

        if (in_array($status, Student::STATUSES, true)) {
            $clauses[] = 'status = :status';
            $parameters['status'] = $status;
        }

        $whereSql = $clauses === [] ? '' : 'WHERE ' . implode(' AND ', $clauses);

        return [$whereSql, $parameters];
    }

    private function query(string $sql): PDOStatement
    {
        $statement = $this->pdo->query($sql);

        if (!$statement instanceof PDOStatement) {
            throw new RuntimeException('Unable to run the requested student query.');
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
            if (is_array($row)) {
                $rows[] = $this->normaliseRow($row);
            }
        }

        return $rows;
    }

    /**
     * @param array<mixed, mixed> $row
     * @return array<string, mixed>
     */
    private function normaliseRow(array $row): array
    {
        $normalised = [];

        foreach ($row as $key => $value) {
            if (is_string($key)) {
                $normalised[$key] = $value;
            }
        }

        return $normalised;
    }

    /**
     * @return array<string, mixed>
     */
    private static function firstRow(PDOStatement $statement): array
    {
        $row = $statement->fetch();

        if (!is_array($row)) {
            return [];
        }

        $normalised = [];

        foreach ($row as $key => $value) {
            if (is_string($key)) {
                $normalised[$key] = $value;
            }
        }

        return $normalised;
    }
}
