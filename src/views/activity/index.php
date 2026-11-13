<?php

declare(strict_types=1);

/**
 * @var array{items: list<array<string, mixed>>, total: int, page: int, pages: int} $logs
 * @var array{total: int, today: int, students_created: int} $stats
 */

$actionStyles = [
    'login' => ['label' => 'Signed in', 'class' => 'secondary', 'icon' => 'bi-box-arrow-in-right'],
    'logout' => ['label' => 'Signed out', 'class' => 'secondary', 'icon' => 'bi-box-arrow-right'],
    'create_student' => ['label' => 'Created', 'class' => 'success', 'icon' => 'bi-plus-circle'],
    'update_student' => ['label' => 'Updated', 'class' => 'info', 'icon' => 'bi-pencil-square'],
    'delete_student' => ['label' => 'Deleted', 'class' => 'danger', 'icon' => 'bi-trash3'],
];
?>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="mini-stat">
            <span class="mini-stat-icon"><i class="bi bi-activity"></i></span>
            <div>
                <strong><?= number_format($stats['total']) ?></strong>
                <span>Total audit events</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <span class="mini-stat-icon stat-icon-amber"><i class="bi bi-calendar-day"></i></span>
            <div>
                <strong><?= number_format($stats['today']) ?></strong>
                <span>Events recorded today</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="mini-stat">
            <span class="mini-stat-icon stat-icon-green"><i class="bi bi-person-plus"></i></span>
            <div>
                <strong><?= number_format($stats['students_created']) ?></strong>
                <span>Students created</span>
            </div>
        </div>
    </div>
</div>

<div class="card panel">
    <div class="panel-header">
        <div>
            <h2>System activity</h2>
            <p>Newest events first &middot; <?= number_format($logs['total']) ?> total</p>
        </div>
        <span class="panel-chip"><i class="bi bi-shield-check"></i> Audit trail</span>
    </div>

    <?php if ($logs['items'] === []): ?>
        <div class="panel-body">
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                <h3>No activity yet</h3>
                <p>Sign-ins and student record changes will appear here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table app-table align-middle">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>User</th>
                        <th class="text-end">IP address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs['items'] as $entry): ?>
                        <?php
                        $action = (string) ($entry['action'] ?? '');
                        $style = $actionStyles[$action] ?? ['label' => ucfirst(str_replace('_', ' ', $action)), 'class' => 'secondary', 'icon' => 'bi-dot'];
                        ?>
                        <tr>
                            <td>
                                <span class="table-primary-text"><?= e(format_date((string) ($entry['created_at'] ?? ''), 'M j, Y')) ?></span>
                                <small class="table-secondary-text"><?= e(format_date((string) ($entry['created_at'] ?? ''), 'g:i A')) ?></small>
                            </td>
                            <td>
                                <span class="badge badge-soft-<?= e($style['class']) ?>">
                                    <i class="bi <?= e($style['icon']) ?> me-1"></i><?= e($style['label']) ?>
                                </span>
                            </td>
                            <td class="activity-description"><?= e((string) ($entry['description'] ?? '')) ?></td>
                            <td><?= e((string) ($entry['user_name'] ?? 'System')) ?></td>
                            <td class="text-end text-muted small"><?= e((string) ($entry['ip_address'] ?? '—')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($logs['pages'] > 1): ?>
            <div class="panel-footer">
                <nav aria-label="Activity pagination">
                    <ul class="pagination app-pagination mb-0">
                        <li class="page-item <?= $logs['page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e(url('activity') . '?page=' . max(1, $logs['page'] - 1)) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php for ($page = 1; $page <= $logs['pages']; $page++): ?>
                            <li class="page-item <?= $page === $logs['page'] ? 'active' : '' ?>">
                                <a class="page-link" href="<?= e(url('activity') . '?page=' . $page) ?>"><?= $page ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $logs['page'] >= $logs['pages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e(url('activity') . '?page=' . min($logs['pages'], $logs['page'] + 1)) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <span class="pagination-copy">Page <?= $logs['page'] ?> of <?= $logs['pages'] ?></span>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
