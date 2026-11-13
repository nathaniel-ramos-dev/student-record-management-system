<?php

declare(strict_types=1);

use App\Models\Student;

/**
 * @var string $mode
 * @var array<string, mixed> $values
 * @var array<string, string> $errors
 * @var \App\Models\Student|null $student
 * @var string $formAction
 * @var string $cancelUrl
 */

$isEdit = $mode === 'edit';
$yearValue = old($values, 'year_level', '1');
?>
<div class="page-actions">
    <a href="<?= e($cancelUrl) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> <?= $isEdit ? 'Back to record' : 'Back to students' ?>
    </a>
</div>

<?php if ($errors !== []): ?>
    <div class="alert alert-danger app-alert" role="alert">
        <i class="bi bi-exclamation-octagon-fill"></i>
        <span>Please review the highlighted fields and try again.</span>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($formAction) ?>" novalidate>
    <?= csrf_field() ?>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card panel mb-3">
                <div class="panel-header">
                    <div>
                        <h2>Personal information</h2>
                        <p>Identity details exactly as they appear on the student's records</p>
                    </div>
                    <span class="panel-step">1</span>
                </div>
                <div class="panel-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="student_no" class="form-label">Student number <span class="required">*</span></label>
                            <input type="text" class="form-control<?= field_class($errors, 'student_no') ?>"
                                   id="student_no" name="student_no" maxlength="30"
                                   value="<?= e(old($values, 'student_no')) ?>" placeholder="2026-00123" required>
                            <?= field_error($errors, 'student_no') ?>
                        </div>

                        <div class="col-md-4">
                            <label for="first_name" class="form-label">First name <span class="required">*</span></label>
                            <input type="text" class="form-control<?= field_class($errors, 'first_name') ?>"
                                   id="first_name" name="first_name" maxlength="80"
                                   value="<?= e(old($values, 'first_name')) ?>" placeholder="Maria" required>
                            <?= field_error($errors, 'first_name') ?>
                        </div>

                        <div class="col-md-4">
                            <label for="middle_name" class="form-label">Middle name</label>
                            <input type="text" class="form-control<?= field_class($errors, 'middle_name') ?>"
                                   id="middle_name" name="middle_name" maxlength="80"
                                   value="<?= e(old($values, 'middle_name')) ?>" placeholder="Santos">
                            <?= field_error($errors, 'middle_name') ?>
                        </div>

                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last name <span class="required">*</span></label>
                            <input type="text" class="form-control<?= field_class($errors, 'last_name') ?>"
                                   id="last_name" name="last_name" maxlength="80"
                                   value="<?= e(old($values, 'last_name')) ?>" placeholder="Dela Cruz" required>
                            <?= field_error($errors, 'last_name') ?>
                        </div>

                        <div class="col-md-3">
                            <label for="gender" class="form-label">Gender <span class="required">*</span></label>
                            <select class="form-select<?= field_class($errors, 'gender') ?>" id="gender" name="gender" required>
                                <option value="">Select gender</option>
                                <?php foreach (Student::GENDERS as $gender): ?>
                                    <option value="<?= e($gender) ?>" <?= old($values, 'gender') === $gender ? 'selected' : '' ?>>
                                        <?= e($gender) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= field_error($errors, 'gender') ?>
                        </div>

                        <div class="col-md-3">
                            <label for="birth_date" class="form-label">Birth date <span class="required">*</span></label>
                            <input type="date" class="form-control<?= field_class($errors, 'birth_date') ?>"
                                   id="birth_date" name="birth_date"
                                   value="<?= e(old($values, 'birth_date')) ?>" max="<?= date('Y-m-d') ?>" required>
                            <?= field_error($errors, 'birth_date') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card panel mb-3">
                <div class="panel-header">
                    <div>
                        <h2>Contact &amp; address</h2>
                        <p>Used for correspondence and record verification</p>
                    </div>
                    <span class="panel-step">2</span>
                </div>
                <div class="panel-body">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label for="email" class="form-label">E-mail address <span class="required">*</span></label>
                            <input type="email" class="form-control<?= field_class($errors, 'email') ?>"
                                   id="email" name="email" maxlength="150"
                                   value="<?= e(old($values, 'email')) ?>" placeholder="maria.delacruz@example.com" required>
                            <?= field_error($errors, 'email') ?>
                        </div>

                        <div class="col-md-5">
                            <label for="phone" class="form-label">Phone number</label>
                            <input type="text" class="form-control<?= field_class($errors, 'phone') ?>"
                                   id="phone" name="phone" maxlength="30"
                                   value="<?= e(old($values, 'phone')) ?>" placeholder="0917 123 4567">
                            <?= field_error($errors, 'phone') ?>
                        </div>

                        <div class="col-12">
                            <label for="address" class="form-label">Complete address</label>
                            <textarea class="form-control<?= field_class($errors, 'address') ?>"
                                      id="address" name="address" rows="3" maxlength="255"
                                      placeholder="House no., street, barangay, city/municipality, province"><?= e(old($values, 'address')) ?></textarea>
                            <?= field_error($errors, 'address') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card panel">
                <div class="panel-header">
                    <div>
                        <h2>Academic information</h2>
                        <p>Programme and enrolment status</p>
                    </div>
                    <span class="panel-step">3</span>
                </div>
                <div class="panel-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="course" class="form-label">Course / programme <span class="required">*</span></label>
                            <select class="form-select<?= field_class($errors, 'course') ?>" id="course" name="course" required>
                                <option value="">Select a course</option>
                                <?php foreach (Student::COURSES as $course): ?>
                                    <option value="<?= e($course) ?>" <?= old($values, 'course') === $course ? 'selected' : '' ?>>
                                        <?= e($course) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= field_error($errors, 'course') ?>
                        </div>

                        <div class="col-md-3">
                            <label for="year_level" class="form-label">Year level <span class="required">*</span></label>
                            <select class="form-select<?= field_class($errors, 'year_level') ?>" id="year_level" name="year_level" required>
                                <?php foreach (Student::YEAR_LEVELS as $level => $label): ?>
                                    <option value="<?= $level ?>" <?= $yearValue === (string) $level ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= field_error($errors, 'year_level') ?>
                        </div>

                        <div class="col-md-3">
                            <label for="status" class="form-label">Status <span class="required">*</span></label>
                            <select class="form-select<?= field_class($errors, 'status') ?>" id="status" name="status" required>
                                <?php foreach (Student::STATUSES as $status): ?>
                                    <option value="<?= e($status) ?>" <?= old($values, 'status', 'Active') === $status ? 'selected' : '' ?>>
                                        <?= e($status) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?= field_error($errors, 'status') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="<?= e($cancelUrl) ?>" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi <?= $isEdit ? 'bi-check2-circle' : 'bi-person-plus' ?>"></i>
                    <?= $isEdit ? 'Save changes' : 'Create student record' ?>
                </button>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card panel form-help">
                <div class="panel-header">
                    <div>
                        <h2>Before you save</h2>
                        <p>Quick checklist</p>
                    </div>
                </div>
                <div class="panel-body">
                    <ul class="help-list">
                        <li><i class="bi bi-asterisk"></i> Fields marked <span class="required">*</span> are required.</li>
                        <li><i class="bi bi-upc-scan"></i> Student numbers must be unique.</li>
                        <li><i class="bi bi-envelope-check"></i> Each e-mail address can only be used once.</li>
                        <li><i class="bi bi-calendar-check"></i> Birth dates cannot be in the future.</li>
                    </ul>

                    <?php if ($isEdit && $student !== null): ?>
                        <hr>
                        <div class="record-meta">
                            <span>Editing record</span>
                            <strong><?= e($student->fullName()) ?></strong>
                            <small><?= e($student->studentNo) ?></small>
                        </div>
                        <div class="record-meta">
                            <span>Created</span>
                            <strong><?= e(format_date($student->createdAt)) ?></strong>
                        </div>
                        <div class="record-meta">
                            <span>Last updated</span>
                            <strong><?= e(format_date($student->updatedAt)) ?></strong>
                        </div>
                    <?php else: ?>
                        <div class="tip-box">
                            <i class="bi bi-lightbulb"></i>
                            <p>Use the format <strong>YYYY-NNNNN</strong> for student numbers, for example <code>2026-00123</code>.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</form>
