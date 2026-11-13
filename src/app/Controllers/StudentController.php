<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Student;
use App\Repositories\ActivityLogRepository;
use App\Repositories\StudentRepository;
use App\Support\ActivityLogger;
use App\Support\Auth;
use App\Support\Config;
use App\Support\Flash;
use App\Support\Validator;
use App\Support\Value;
use App\Support\View;

final class StudentController
{
    public function __construct(
        private readonly StudentRepository $students,
        private readonly ActivityLogRepository $activity,
    ) {
    }

    public function index(): void
    {
        Auth::require();

        $filters = $this->filters();
        $page = max(1, Value::int($_GET, 'page', 1));
        $perPage = max(1, Config::int('app.per_page', 10));

        View::render('students/index', [
            'pageTitle'    => 'Students',
            'pageSubtitle' => 'Search, filter and manage every student record',
            'students'     => $this->students->paginate($filters, $page, $perPage),
            'filters'      => $filters,
            'courses'      => $this->students->distinctCourses(),
        ]);
    }

    public function create(): void
    {
        Auth::require();

        $this->renderForm('create', [
            'status'     => 'Active',
            'gender'     => 'Male',
            'year_level' => '1',
        ], []);
    }

    public function store(): void
    {
        Auth::require();

        $input = $this->studentInput();
        $student = Student::fromInput($input);
        $errors = $this->validate($input, null);

        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm('create', $input, $errors);

            return;
        }

        $id = $this->students->create($student);

        ActivityLogger::log(
            'create_student',
            'student',
            $id,
            sprintf('Created student record %s — %s.', $student->studentNo, $student->fullName()),
        );

        Flash::success(sprintf('Student record for %s was created.', $student->fullName()));
        redirect('/students/' . $id);
    }

    public function show(string $id): void
    {
        Auth::require();

        $student = $this->findOrFail($id);

        View::render('students/show', [
            'pageTitle'    => $student->fullName(),
            'pageSubtitle' => 'Student record ' . $student->studentNo,
            'student'      => $student,
            'activities'   => $this->activity->forEntity('student', (int) $student->id),
        ]);
    }

    public function edit(string $id): void
    {
        Auth::require();

        $student = $this->findOrFail($id);

        $this->renderForm('edit', $student->toDatabase(), [], $student);
    }

    public function update(string $id): void
    {
        Auth::require();

        $student = $this->findOrFail($id);
        $input = $this->studentInput();
        $errors = $this->validate($input, (int) $student->id);

        if ($errors !== []) {
            http_response_code(422);
            $this->renderForm('edit', $input, $errors, $student);

            return;
        }

        $updated = Student::fromInput($input, (int) $student->id);
        $this->students->update((int) $student->id, $updated);

        ActivityLogger::log(
            'update_student',
            'student',
            (int) $student->id,
            sprintf('Updated student record %s — %s.', $updated->studentNo, $updated->fullName()),
        );

        Flash::success('Student record updated successfully.');
        redirect('/students/' . $student->id);
    }

    public function destroy(string $id): void
    {
        Auth::require();

        $student = $this->findOrFail($id);
        $this->students->delete((int) $student->id);

        ActivityLogger::log(
            'delete_student',
            'student',
            (int) $student->id,
            sprintf('Deleted student record %s — %s.', $student->studentNo, $student->fullName()),
        );

        Flash::success(sprintf('Student record for %s was deleted.', $student->fullName()));
        redirect('/students');
    }

    public function export(): void
    {
        Auth::require();

        $rows = $this->students->forExport($this->filters());

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="students-' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'wb');

        if ($output === false) {
            return;
        }

        // UTF-8 BOM keeps Excel happy with names such as "Peña".
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Student No.', 'Last Name', 'First Name', 'Middle Name', 'E-mail', 'Phone',
            'Gender', 'Birth Date', 'Age', 'Course', 'Year Level', 'Address', 'Status', 'Date Enrolled',
        ]);

        foreach ($rows as $student) {
            fputcsv($output, [
                $student->studentNo,
                $student->lastName,
                $student->firstName,
                $student->middleName,
                $student->email,
                $student->phone,
                $student->gender,
                $student->birthDate,
                $student->age(),
                $student->course,
                $student->yearLabel(),
                $student->address,
                $student->status,
                $student->createdAt,
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string> $errors
     */
    private function renderForm(string $mode, array $values, array $errors, ?Student $student = null): void
    {
        $isEdit = $mode === 'edit';

        View::render('students/form', [
            'pageTitle'    => $isEdit ? 'Edit student' : 'Add student',
            'pageSubtitle' => $isEdit ? 'Update the selected student record' : 'Create a new student record',
            'mode'         => $mode,
            'values'       => $values,
            'errors'       => $errors,
            'student'      => $student,
            'formAction'   => $isEdit && $student !== null
                ? url('students/' . $student->id)
                : url('students'),
            'cancelUrl'    => $isEdit && $student !== null
                ? url('students/' . $student->id)
                : url('students'),
        ]);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    private function validate(array $input, ?int $id): array
    {
        $validator = new Validator($input);

        $validator
            ->required('student_no', 'Student number')
            ->max('student_no', 'Student number', 30)
            ->regex(
                'student_no',
                'Student number',
                '/^[A-Za-z0-9][A-Za-z0-9._-]*$/',
                'may only contain letters, numbers, dots, hyphens and underscores.',
            )
            ->required('first_name', 'First name')
            ->max('first_name', 'First name', 80)
            ->max('middle_name', 'Middle name', 80)
            ->required('last_name', 'Last name')
            ->max('last_name', 'Last name', 80)
            ->required('email', 'E-mail address')
            ->email('email', 'E-mail address')
            ->max('email', 'E-mail address', 150)
            ->max('phone', 'Phone number', 30)
            ->required('gender', 'Gender')
            ->in('gender', 'Gender', Student::GENDERS)
            ->required('birth_date', 'Birth date')
            ->date('birth_date', 'Birth date')
            ->notInFuture('birth_date', 'Birth date')
            ->required('course', 'Course')
            ->in('course', 'Course', Student::COURSES)
            ->required('year_level', 'Year level')
            ->integerBetween('year_level', 'Year level', 1, 5)
            ->max('address', 'Address', 255)
            ->required('status', 'Status')
            ->in('status', 'Status', Student::STATUSES);

        $errors = $validator->errors();

        $studentNo = Value::string($input, 'student_no');

        if (!isset($errors['student_no']) && $studentNo !== '' && $this->students->studentNoExists($studentNo, $id)) {
            $errors['student_no'] = 'That student number is already used by another record.';
        }

        $email = Value::string($input, 'email');

        if (!isset($errors['email']) && $email !== '' && $this->students->emailExists($email, $id)) {
            $errors['email'] = 'That e-mail address is already used by another record.';
        }

        return $errors;
    }

    /**
     * @return array{search: string, course: string, year_level: string, status: string}
     */
    private function filters(): array
    {
        $status = Value::string($_GET, 'status');

        return [
            'search'     => Value::string($_GET, 'search'),
            'course'     => Value::string($_GET, 'course'),
            'year_level' => Value::string($_GET, 'year_level'),
            'status'     => in_array($status, Student::STATUSES, true) ? $status : '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function studentInput(): array
    {
        return [
            'student_no' => Value::string($_POST, 'student_no'),
            'first_name' => Value::string($_POST, 'first_name'),
            'middle_name' => Value::string($_POST, 'middle_name'),
            'last_name'  => Value::string($_POST, 'last_name'),
            'email'      => Value::string($_POST, 'email'),
            'phone'      => Value::string($_POST, 'phone'),
            'gender'     => Value::string($_POST, 'gender'),
            'birth_date' => Value::string($_POST, 'birth_date'),
            'course'     => Value::string($_POST, 'course'),
            'year_level' => Value::string($_POST, 'year_level'),
            'address'    => Value::string($_POST, 'address'),
            'status'     => Value::string($_POST, 'status', 'Active'),
        ];
    }

    private function findOrFail(string $id): Student
    {
        $student = ctype_digit($id) ? $this->students->find((int) $id) : null;

        if (!$student instanceof Student) {
            http_response_code(404);
            View::render('errors/404', ['pageTitle' => 'Student not found'], 'error-layout');
            exit;
        }

        return $student;
    }
}
