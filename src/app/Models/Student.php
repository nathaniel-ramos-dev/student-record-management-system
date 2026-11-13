<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Value;
use DateTimeImmutable;
use Exception;

/**
 * Immutable student record.
 */
final readonly class Student
{
    public const GENDERS = ['Male', 'Female', 'Other'];

    public const STATUSES = ['Active', 'Inactive', 'Graduated'];

    public const COURSES = [
        'BS Information Technology',
        'BS Computer Science',
        'BS Information Systems',
        'BS Accountancy',
        'BS Business Administration',
        'BS Civil Engineering',
        'BS Nursing',
        'BS Education',
        'BS Criminology',
        'BS Hospitality Management',
    ];

    public const YEAR_LEVELS = [
        1 => '1st Year',
        2 => '2nd Year',
        3 => '3rd Year',
        4 => '4th Year',
        5 => '5th Year',
    ];

    public function __construct(
        public ?int $id,
        public string $studentNo,
        public string $firstName,
        public string $middleName,
        public string $lastName,
        public string $email,
        public string $phone,
        public string $gender,
        public string $birthDate,
        public string $course,
        public int $yearLevel,
        public string $address,
        public string $status,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromRow(array $row): self
    {
        return new self(
            id: Value::int($row, 'id'),
            studentNo: Value::string($row, 'student_no'),
            firstName: Value::string($row, 'first_name'),
            middleName: Value::string($row, 'middle_name'),
            lastName: Value::string($row, 'last_name'),
            email: Value::string($row, 'email'),
            phone: Value::string($row, 'phone'),
            gender: Value::string($row, 'gender'),
            birthDate: Value::string($row, 'birth_date'),
            course: Value::string($row, 'course'),
            yearLevel: Value::int($row, 'year_level'),
            address: Value::string($row, 'address'),
            status: Value::string($row, 'status', 'Active'),
            createdAt: isset($row['created_at']) ? Value::string($row, 'created_at') : null,
            updatedAt: isset($row['updated_at']) ? Value::string($row, 'updated_at') : null,
        );
    }

    /**
     * @param array<string, mixed> $input
     */
    public static function fromInput(array $input, ?int $id = null): self
    {
        return new self(
            id: $id,
            studentNo: Value::string($input, 'student_no'),
            firstName: Value::string($input, 'first_name'),
            middleName: Value::string($input, 'middle_name'),
            lastName: Value::string($input, 'last_name'),
            email: Value::string($input, 'email'),
            phone: Value::string($input, 'phone'),
            gender: Value::string($input, 'gender'),
            birthDate: Value::string($input, 'birth_date'),
            course: Value::string($input, 'course'),
            yearLevel: Value::int($input, 'year_level'),
            address: Value::string($input, 'address'),
            status: Value::string($input, 'status', 'Active'),
        );
    }

    /**
     * @return array<string, string|int|null>
     */
    public function toDatabase(): array
    {
        return [
            'student_no' => $this->studentNo,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName !== '' ? $this->middleName : null,
            'last_name'  => $this->lastName,
            'email'      => $this->email,
            'phone'      => $this->phone !== '' ? $this->phone : null,
            'gender'     => $this->gender,
            'birth_date' => $this->birthDate !== '' ? $this->birthDate : null,
            'course'     => $this->course,
            'year_level' => $this->yearLevel,
            'address'    => $this->address !== '' ? $this->address : null,
            'status'     => $this->status,
        ];
    }

    public function fullName(): string
    {
        $middle = $this->middleName !== '' ? ' ' . mb_substr($this->middleName, 0, 1) . '.' : '';

        return trim($this->firstName . $middle . ' ' . $this->lastName);
    }

    public function age(): ?int
    {
        if ($this->birthDate === '') {
            return null;
        }

        try {
            $birthDate = new DateTimeImmutable($this->birthDate);
        } catch (Exception) {
            return null;
        }

        return $birthDate->diff(new DateTimeImmutable('today'))->y;
    }

    public function yearLabel(): string
    {
        return self::YEAR_LEVELS[$this->yearLevel] ?? 'Year ' . $this->yearLevel;
    }
}
