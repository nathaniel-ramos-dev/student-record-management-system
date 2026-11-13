<?php

declare(strict_types=1);

use App\Models\Student;

/**
 * @var array{items: list<Student>, total: int, page: int, perPage: int, pages: int} $students
 * @var array{search: string, course: string, year_level: string, status: string} $filters
 * @var list<string> $courses
 */

$activeFilters = array_filter($filters, static fn (string $value): bool => $value !== '');
$queryString = http_build_query($activeFilters);

$pageUrl = static function (int $page) use ($activeFilters): string {
    return url('students') . '?' . http_build_query(array_merge($activeFilters, ['page' => $page]));
};

$exportUrl = url('students/export') . ($queryString !== '' ? '?' . $queryString : '');
$firstItem = $students['total'] === 0 ? 0 : (($students['page'] - 1) * $students['perPage']) + 1;
$lastItem = min($students['total'], $students['page'] * $students['perPage']);

$startPage = max(1, $students['page'] - 2);
$endPage = min($students['pages'], $startPage + 4);
$startPage = max(1, $endPage - 4);
?>
<div class="page-actions">
    <a href="<?= e(url('students/create')) ?>" class="btn btn-primary">
        <i class="bi bi-person-plus"></i> Add student
    </a>
    <a href="<?= e($exportUrl) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-download"></i> Export
    </a>
</div>

<div class="card panel filter-panel">
    <form method="get" action="<?= e(url('students')) ?>" class="filter-form">
        <div class="filter-search">
            <i class="bi bi-search"></i>
            <input type="search" name="search" class="form-control" placeholder="Search by name, student number or e-mail"
                   value="<?= e($filters['search']) ?>" aria-label="Search students">
        </div>

        <select name="course" class="form-select" aria-label="Filter by course">
            <option value="">All courses</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= e($course) ?>" <?= $filters['course'] === $course ? 'selected' : '' ?>>
                    <?= e($course) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="year_level" class="form-select" aria-label="Filter by year level">
            <option value="">All year levels</option>
            <?php foreach (Student::YEAR_LEVELS as $level => $label): ?>
                <option value="<?= $level ?>" <?= $filters['year_level'] === (string) $level ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="form-select" aria-label="Filter by status">
            <option value="">All statuses</option>
            <?php foreach (Student::STATUSES as $status): ?>
                <option value="<?= e($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>>
                    <?= e($status) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i> Apply</button>
            <?php if ($activeFilters !== []): ?>
                <a href="<?= e(url('students')) ?>" class="btn btn-outline-secondary" title="Clear filters">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<div class="card panel">
    <div class="panel-header">
        <div>
            <h2>Student directory</h2>
            <p>
                <?php if ($students['total'] === 0): ?>
                    No records match the current filters
                <?php else: ?>
                    Showing <?= number_format($firstItem) ?>&ndash;<?= number_format($lastItem) ?>
                    of <?= number_format($students['total']) ?> record<?= $students['total'] === 1 ? '' : 's' ?>
                <?php endif; ?>
            </p>
        </div>
        <?php if ($activeFilters !== []): ?>
            <span class="panel-chip"><i class="bi bi-funnel-fill"></i> Filtered</span>
        <?php endif; ?>
    </div>

    <?php if ($students['items'] === []): ?>
        <div class="panel-body">
            <div class="empty-state">
                <i class="bi bi-search"></i>
                <h3>No students found</h3>
                <p>
                    <?= $activeFilters === []
                        ? 'There are no student records yet. Add the first one to get started.'
                        : 'Try a different search term or clear the filters.' ?>
                </p>
                <?php if ($activeFilters === []): ?>
                    <a href="<?= e(url('students/create')) ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Add student
                    </a>
                <?php else: ?>
                    <a href="<?= e(url('students')) ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-lg"></i> Clear filters
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table app-table align-middle">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course &amp; year</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students['items'] as $student): ?>
                        <tr>
                            <td>
                                <a class="student-cell" href="<?= e(url('students/' . $student->id)) ?>">
                                    <span class="avatar"><?= e(initials($student->firstName, $student->lastName)) ?></span>
                                    <span>
                                        <strong><?= e($student->fullName()) ?></strong>
                                        <small><?= e($student->studentNo) ?></small>
                                    </span>
                                </a>
                            </td>
                            <td>
                                <span class="table-primary-text"><?= e($student->course) ?></span>
                                <small class="table-secondary-text"><?= e($student->yearLabel()) ?></small>
                            </td>
                            <td>
                                <span class="table-primary-text"><?= e($student->email) ?></span>
                                <small class="table-secondary-text"><?= e($student->phone !== '' ? $student->phone : 'No phone number') ?></small>
                            </td>
                            <td>
                                <span class="badge badge-soft-<?= e(strtolower($student->status)) ?>">
                                    <?= e($student->status) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="row-actions">
                                    <a href="<?= e(url('students/' . $student->id)) ?>" class="btn btn-icon-sm"
                                       title="View record" aria-label="View record">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= e(url('students/' . $student->id . '/edit')) ?>" class="btn btn-icon-sm"
                                       title="Edit record" aria-label="Edit record">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="post" action="<?= e(url('students/' . $student->id . '/delete')) ?>"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete the record for <?= e(addslashes($student->fullName())) ?>? This cannot be undone.');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-icon-sm btn-icon-danger"
                                                title="Delete record" aria-label="Delete record">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($students['pages'] > 1): ?>
            <div class="panel-footer">
                <nav aria-label="Student pagination">
                    <ul class="pagination app-pagination mb-0">
                        <li class="page-item <?= $students['page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e($pageUrl(max(1, $students['page'] - 1))) ?>" aria-label="Previous">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>

                        <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                            <li class="page-item <?= $page === $students['page'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= e($pageUrl($page)) ?>"><?= $page ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= $students['page'] >= $students['pages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e($pageUrl(min($students['pages'], $students['page'] + 1))) ?>" aria-label="Next">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <span class="pagination-copy">Page <?= $students['page'] ?> of <?= $students['pages'] ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
