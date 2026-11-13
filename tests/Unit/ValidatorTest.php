<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\Validator;
use PHPUnit\Framework\TestCase;

final class ValidatorTest extends TestCase
{
    public function testRequiredFieldFailsWhenEmpty(): void
    {
        $validator = new Validator([]);
        $validator->required('first_name', 'First name');

        self::assertTrue($validator->fails());
        self::assertSame(
            ['first_name' => 'First name is required.'],
            $validator->errors(),
        );
    }

    public function testValidPayloadPasses(): void
    {
        $validator = new Validator([
            'student_no' => '2026-00123',
            'email'      => 'maria@example.com',
            'gender'     => 'Female',
            'birth_date' => '2005-04-12',
            'year_level' => '3',
            'status'     => 'Active',
        ]);

        $validator
            ->required('student_no', 'Student number')
            ->required('email', 'E-mail address')
            ->email('email', 'E-mail address')
            ->in('gender', 'Gender', ['Male', 'Female', 'Other'])
            ->date('birth_date', 'Birth date')
            ->notInFuture('birth_date', 'Birth date')
            ->integerBetween('year_level', 'Year level', 1, 5)
            ->in('status', 'Status', ['Active', 'Inactive', 'Graduated']);

        self::assertFalse($validator->fails());
        self::assertSame([], $validator->errors());
    }

    public function testInvalidEmailIsRejected(): void
    {
        $validator = new Validator(['email' => 'not-an-email']);
        $validator->email('email', 'E-mail address');

        self::assertArrayHasKey('email', $validator->errors());
    }

    public function testImpossibleDateIsRejected(): void
    {
        $validator = new Validator(['birth_date' => '2005-02-30']);
        $validator->date('birth_date', 'Birth date');

        self::assertArrayHasKey('birth_date', $validator->errors());
    }

    public function testFutureDateIsRejected(): void
    {
        $validator = new Validator(['birth_date' => (new \DateTimeImmutable('+1 day'))->format('Y-m-d')]);
        $validator->notInFuture('birth_date', 'Birth date');

        self::assertArrayHasKey('birth_date', $validator->errors());
    }

    public function testOutOfRangeYearLevelIsRejected(): void
    {
        $validator = new Validator(['year_level' => '9']);
        $validator->integerBetween('year_level', 'Year level', 1, 5);

        self::assertSame(
            ['year_level' => 'Year level must be between 1 and 5.'],
            $validator->errors(),
        );
    }

    public function testOnlyFirstErrorPerFieldIsKept(): void
    {
        $validator = new Validator(['name' => '']);
        $validator->required('name', 'Name')->max('name', 'Name', 5);

        self::assertCount(1, $validator->errors());
        self::assertSame('Name is required.', $validator->errors()['name']);
    }

    public function testValuesAreTrimmed(): void
    {
        $validator = new Validator(['name' => '  Maria  ']);

        self::assertSame('Maria', $validator->value('name'));
    }
}
