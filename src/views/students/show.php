<?php

declare(strict_types=1);

use App\Models\Student;

/**
 * @var Student $student
 * @var list<array<string, mixed>> $activities
 */

$actionStyles = [
    'create_student' => ['label' => 'Created', 'class' => 'success', 'icon' => 'bi-plus-circle'],
    'update_student' => ['label' => 'Updated', 'class' => 'info', 'icon' => 'bi-pencil-square'],
    'delete_student' => ['label' => 'Deleted', 'class' => 'danger', 'icon' => 'bi-trash3'],
];
?>
<div class="page-actions">
    <a href="<?= e(url('students')) ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> All students
    </a>
    <a href="<?= e(url('students/' . $student->id . '/edit')) ?>" class="btn btn-primary">
        <i class="bi bi-pencil"></i> Edit record
    </a>
    <form method="post" action="<?= e(url('students/' . $student->id . '/delete')) ?>"
          onsubmit="return confirm('Delete the record for <?= e(addslashes($student->fullName())) ?>? This cannot be undone.');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger">
            <i class="bi bi-trash3"></i> Delete
        </button>
    </form>
</div>

<div class="row g-3">
    <div class="col-xl-4">
        <div class="card panel profile-card mb-3">
            <div class="profile-hero">
                <span class="avatar avatar-xl"><?= e(initials($student->firstName, $student->lastName)) ?></span>
                <h2><?= e($student->fullName()) ?></h2>
                <span class="profile-number"><?= e($student->studentNo) ?></span>
                <span class="badge badge-soft-<?= e(strtolower($student->status)) ?>"><?= e($student->status) ?></span>
            </div>
            <div class="panel-body">
                <div class="profile-course">
                    <i class="bi bi-journal-bookmark-fill"></i>
                    <div>
                        <strong><?= e($student->course) ?></strong>
                        <span><?= e($student->yearLabel()) ?></span>
                    </div>
                </div>

                <div class="detail-list">
                    <div class="detail-row">
                        <span class="detail-label"><i class="bi bi-envelope"></i> E-mail</span>
                        <span class="detail-value"><a href="mailto:<?= e($student->email) ?>"><?= e($student->email) ?></a></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="bi bi-telephone"></i> Phone</span>
                        <span class="detail-value"><?= e($student->phone !== '' ? $student->phone : '—') ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label"><i class="bi bi-geo-alt"></i> Address</span>
                        <span class="detail-value"><?= e($student->address !== '' ? $student->address : '—') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card panel">
            <div class="panel-header">
                <div>
                    <h2>Personal details</h2>
                    <p>Identity and demographics</p>
                </div>
            </div>
            <div class="panel-body">
                <div class="detail-list">
                    <div class="detail-row">
                        <span class="detail-label">Gender</span>
                        <span class="detail-value"><?= e($student->gender) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Birth date</span>
                        <span class="detail-value"><?= e(format_date($student->birthDate)) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Age</span>
                        <span class="detail-value"><?= $student->age() !== null ? e((string) $student->age()) . ' years old' : '—' ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date enrolled</span>
                        <span class="detail-value"><?= e(format_date($student->createdAt)) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card panel mb-3">
            <div class="panel-header">
                <div>
                    <h2>Enrolment summary</h2>
                    <p>Current academic standing of this student</p>
                </div>
                <span class="panel-chip">ID #<?= (int) $student->id ?></span>
            </div>
            <div class="panel-body">
                <div class="summary-grid">
                    <div class="summary-tile">
                        <span class="summary-icon"><i class="bi bi-journal-bookmark"></i></span>
                        <span class="summary-label">Course</span>
                        <strong><?= e($student->course) ?></strong>
                    </div>
                    <div class="summary-tile">
                        <span class="summary-icon"><i class="bi bi-layers"></i></span>
                        <span class="summary-label">Year level</span>
                        <strong><?= e($student->yearLabel()) ?></strong>
                    </div>
                    <div class="summary-tile">
                        <span class="summary-icon"><i class="bi bi-toggle-on"></i></span>
                        <span class="summary-label">Status</span>
                        <strong><?= e($student->status) ?></strong>
                    </div>
                    <div class="summary-tile">
                        <span class="summary-icon"><i class="bi bi-clock"></i></span>
                        <span class="summary-label">Last updated</span>
                        <strong><?= e(format_date($student->updatedAt)) ?></strong>
                    </div>
                </div>

                <?php if ($student->status === 'Inactive'): ?>
                    <div class="alert alert-warning app-alert mt-3 mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span>This student record is currently marked <strong>inactive</strong>.</span>
                    </div>
                <?php elseif ($student->status === 'Graduated'): ?>
                    <div class="alert alert-info app-alert mt-3 mb-0">
                        <i class="bi bi-mortarboard-fill"></i>
                        <span>This student has already <strong>graduated</strong>.</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card panel">
            <div class="panel-header">
                <div>
                    <h2>Record history</h2>
                    <p>Audit trail for this student</p>
                </div>
                <a href="<?= e(url('activity')) ?>" class="panel-link">Full log</a>
            </div>
            <div class="panel-body">
                <?php if ($activities === []): ?>
                    <div class="empty-state py-4">
                        <i class="bi bi-clock-history"></i>
                        <p>No activity has been recorded for this student yet.</p>
                    </div>
                <?php else: ?>
                    <ul class="timeline">
                        <?php foreach ($activities as $entry): ?>
                            <?php
                            $action = (string) ($entry['action'] ?? '');
                            $style = $actionStyles[$action] ?? ['label' => ucfirst(str_replace('_', ' ', $action)), 'class' => 'secondary', 'icon' => 'bi-dot'];
                            ?>
                            <li class="timeline-item">
                                <span class="timeline-dot timeline-<?= e($style['class']) ?>">
                                    <i class="bi <?= e($style['icon']) ?>"></i>
                                </span>
                                <div class="timeline-content">
                                    <div class="timeline-head">
                                        <strong><?= e($style['label']) ?> record</strong>
                                        <span><?= e(format_date((string) ($entry['created_at'] ?? ''), 'M j, Y g:i A')) ?></span>
                                    </div>
                                    <p><?= e((string) ($entry['description'] ?? '')) ?></p>
                                    <small>
                                        by <?= e((string) ($entry['user_name'] ?? 'System')) ?>
                                        <?php if (($entry['ip_address'] ?? '') !== ''): ?>
                                            &middot; <?= e((string) $entry['ip_address']) ?>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
