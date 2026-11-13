<?php

declare(strict_types=1);

use App\Models\Student;

/**
 * @var array{total: int, active: int, inactive: int, graduated: int, male: int, female: int, new_this_month: int} $stats
 * @var list<array{course: string, total: int}> $byCourse
 * @var array<int, int> $byYearLevel
 * @var list<Student> $recent
 * @var array{total: int, today: int, students_created: int} $activityStats
 */

$total = max(0, $stats['total']);
$yearTotal = max(1, array_sum($byYearLevel));
$otherGender = max(0, $total - $stats['male'] - $stats['female']);
$maxCourse = 1;

foreach ($byCourse as $courseRow) {
    $maxCourse = max($maxCourse, $courseRow['total']);
}

$malePercent = $total > 0 ? round($stats['male'] / $total * 100) : 0;
$femalePercent = $total > 0 ? round($stats['female'] / $total * 100) : 0;
$otherPercent = $total > 0 ? max(0, 100 - $malePercent - $femalePercent) : 0;
?>
<div class="page-actions">
    <a href="<?= e(url('students/export')) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-download"></i> Export CSV
    </a>
    <a href="<?= e(url('students/create')) ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Add student
    </a>
</div>

<div class="row g-3">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon stat-icon-indigo"><i class="bi bi-people-fill"></i></div>
            <div class="stat-body">
                <span class="stat-label">Total students</span>
                <strong class="stat-value"><?= number_format($total) ?></strong>
                <span class="stat-note">All records in the database</span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon stat-icon-green"><i class="bi bi-person-check-fill"></i></div>
            <div class="stat-body">
                <span class="stat-label">Active students</span>
                <strong class="stat-value"><?= number_format($stats['active']) ?></strong>
                <span class="stat-note"><?= number_format($stats['inactive']) ?> inactive records</span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon stat-icon-amber"><i class="bi bi-stars"></i></div>
            <div class="stat-body">
                <span class="stat-label">New this month</span>
                <strong class="stat-value"><?= number_format($stats['new_this_month']) ?></strong>
                <span class="stat-note">Records added since the 1st</span>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon stat-icon-blue"><i class="bi bi-mortarboard-fill"></i></div>
            <div class="stat-body">
                <span class="stat-label">Graduated</span>
                <strong class="stat-value"><?= number_format($stats['graduated']) ?></strong>
                <span class="stat-note">Completed their programme</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-7">
        <div class="card panel h-100">
            <div class="panel-header">
                <div>
                    <h2>Students by year level</h2>
                    <p>Distribution across the five year levels</p>
                </div>
                <span class="panel-chip"><?= number_format($total) ?> total</span>
            </div>

            <div class="panel-body">
                <?php if ($total === 0): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-bar-chart"></i>
                        <p>No enrolment data yet. Add your first student to see the chart.</p>
                    </div>
                <?php else: ?>
                    <div class="year-bars">
                        <?php foreach (Student::YEAR_LEVELS as $level => $label): ?>
                            <?php
                            $count = $byYearLevel[$level] ?? 0;
                            $percent = (int) round($count / $yearTotal * 100);
                            ?>
                            <div class="year-bar-row">
                                <span class="year-bar-label"><?= e($label) ?></span>
                                <div class="year-bar-track">
                                    <div class="year-bar-fill" style="width: <?= $percent ?>%"></div>
                                </div>
                                <span class="year-bar-value"><?= number_format($count) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card panel h-100">
            <div class="panel-header">
                <div>
                    <h2>Students by course</h2>
                    <p>Top programmes by enrolment</p>
                </div>
            </div>

            <div class="panel-body">
                <?php if ($byCourse === []): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-journal-bookmark"></i>
                        <p>Course distribution will appear once records are added.</p>
                    </div>
                <?php else: ?>
                    <div class="course-list">
                        <?php foreach (array_slice($byCourse, 0, 5) as $courseRow): ?>
                            <?php $width = (int) round($courseRow['total'] / $maxCourse * 100); ?>
                            <div class="course-row">
                                <div class="course-row-head">
                                    <span><?= e($courseRow['course']) ?></span>
                                    <strong><?= number_format($courseRow['total']) ?></strong>
                                </div>
                                <div class="course-track">
                                    <div class="course-fill" style="width: <?= $width ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-8">
        <div class="card panel h-100">
            <div class="panel-header">
                <div>
                    <h2>Recently added students</h2>
                    <p>The five newest records in the system</p>
                </div>
                <a href="<?= e(url('students')) ?>" class="btn btn-sm btn-outline-secondary">
                    View all <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <?php if ($recent === []): ?>
                <div class="panel-body">
                    <div class="empty-state">
                        <i class="bi bi-person-plus"></i>
                        <h3>No students yet</h3>
                        <p>Start by adding a student record to the system.</p>
                        <a href="<?= e(url('students/create')) ?>" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg"></i> Add student
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table app-table align-middle">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th class="text-end">Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $student): ?>
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
                                    <td class="text-muted"><?= e($student->course) ?></td>
                                    <td><?= e($student->yearLabel()) ?></td>
                                    <td>
                                        <span class="badge badge-soft-<?= e(strtolower($student->status)) ?>">
                                            <?= e($student->status) ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-muted small"><?= e(format_date($student->createdAt)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card panel mb-3">
            <div class="panel-header">
                <div>
                    <h2>Gender distribution</h2>
                    <p>Across all student records</p>
                </div>
            </div>
            <div class="panel-body">
                <?php if ($total === 0): ?>
                    <div class="empty-state py-3">
                        <i class="bi bi-gender-ambiguous"></i>
                        <p>No data available yet.</p>
                    </div>
                <?php else: ?>
                    <div class="gender-chart">
                        <div class="gender-bar">
                            <span class="gender-male" style="width: <?= $malePercent ?>%"></span>
                            <span class="gender-female" style="width: <?= $femalePercent ?>%"></span>
                            <span class="gender-other" style="width: <?= $otherPercent ?>%"></span>
                        </div>
                        <ul class="gender-legend">
                            <li><span class="dot dot-male"></span> Male <strong><?= number_format($stats['male']) ?> &middot; <?= $malePercent ?>%</strong></li>
                            <li><span class="dot dot-female"></span> Female <strong><?= number_format($stats['female']) ?> &middot; <?= $femalePercent ?>%</strong></li>
                            <li><span class="dot dot-other"></span> Other <strong><?= number_format($otherGender) ?> &middot; <?= $otherPercent ?>%</strong></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card panel">
            <div class="panel-header">
                <div>
                    <h2>Audit snapshot</h2>
                    <p>System activity at a glance</p>
                </div>
                <a href="<?= e(url('activity')) ?>" class="panel-link">Open log</a>
            </div>
            <div class="panel-body">
                <div class="mini-metrics">
                    <div>
                        <strong><?= number_format($activityStats['total']) ?></strong>
                        <span>Total events</span>
                    </div>
                    <div>
                        <strong><?= number_format($activityStats['today']) ?></strong>
                        <span>Events today</span>
                    </div>
                    <div>
                        <strong><?= number_format($activityStats['students_created']) ?></strong>
                        <span>Records created</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
