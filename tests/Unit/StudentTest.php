<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Student;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class StudentTest extends TestCase
{
    public function testFromInputTrimsValues(): void
    {
        $student = Student::fromInput([
            'student_no' => '  2026-00123  ',
            'first_name' => ' Maria ',
            'middle_name' => ' Santos ',
            'last_name'  => ' Dela Cruz ',
            'email'      => ' maria@example.com ',
            'phone'      => ' 09171234567 ',
            'gender'     => 'Female',
            'birth_date' => '2005-04-12',
            'course'     => 'BS Information Technology',
            'year_level' => '3',
            'address'    => ' Catbalogan City ',
            'status'     => 'Active',
        ], 7);

        self::assertSame(7, $student->id);
        self::assertSame('2026-00123', $student->studentNo);
        self::assertSame('Maria', $student->firstName);
        self::assertSame(3, $student->yearLevel);
    }

    public function testFullNameUsesMiddleInitial(): void
    {
        $student = new Student(
            id: 2,
            studentNo: '2026-00124',
            firstName: 'Maria',
            middleName: 'Santos',
            lastName: 'Dela Cruz',
            email: 'maria.santos@example.com',
            phone: '',
            gender: 'Female',
            birthDate: '2005-04-12',
            course: 'BS Information Technology',
            yearLevel: 3,
            address: '',
            status: 'Active',
        );

        self::assertSame('Maria S. Dela Cruz', $student->fullName());
    }

    public function testToDatabaseConvertsBlankOptionalValuesToNull(): void
    {
        $student = $this->student();

        $data = $student->toDatabase();

        self::assertNull($data['middle_name']);
        self::assertNull($data['phone']);
        self::assertNull($data['address']);
    }

    public function testAgeIsCalculatedFromBirthDate(): void
    {
        $student = $this->student();

        $expected = (new DateTimeImmutable('2005-04-12'))
            ->diff(new DateTimeImmutable('today'))
            ->y;

        self::assertSame($expected, $student->age());
    }

    public function testYearLabel(): void
    {
        self::assertSame('3rd Year', $this->student()->yearLabel());
    }

    public function testFromRowMapsDatabaseColumns(): void
    {
        $student = Student::fromRow([
            'id'          => '11',
            'student_no'  => '2024-00111',
            'first_name'  => 'Juan',
            'middle_name' => null,
            'last_name'   => 'Tamad',
            'email'       => 'juan@example.com',
            'phone'       => null,
            'gender'      => 'Male',
            'birth_date'  => '2004-01-01',
            'course'      => 'BS Computer Science',
            'year_level'  => '2',
            'address'     => null,
            'status'      => 'Active',
            'created_at'  => '2026-01-01 08:00:00',
            'updated_at'  => '2026-01-01 08:00:00',
        ]);

        self::assertSame(11, $student->id);
        self::assertSame('Juan Tamad', $student->fullName());
        self::assertSame('', $student->middleName);
        self::assertSame(2, $student->yearLevel);
    }

    private function student(): Student
    {
        return new Student(
            id: 1,
            studentNo: '2026-00123',
            firstName: 'Maria',
            middleName: '',
            lastName: 'Dela Cruz',
            email: 'maria@example.com',
            phone: '',
            gender: 'Female',
            birthDate: '2005-04-12',
            course: 'BS Information Technology',
            yearLevel: 3,
            address: '',
            status: 'Active',
        );
    }
}
