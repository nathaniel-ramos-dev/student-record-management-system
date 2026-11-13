<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ActivityLogRepository;
use App\Support\Auth;
use App\Support\Value;
use App\Support\View;

final class ActivityController
{
    public function __construct(private readonly ActivityLogRepository $activity)
    {
    }

    public function index(): void
    {
        Auth::require();

        $page = max(1, Value::int($_GET, 'page', 1));

        View::render('activity/index', [
            'pageTitle'    => 'Activity Log',
            'pageSubtitle' => 'Audit trail of every change made in the system',
            'logs'         => $this->activity->paginate($page, 15),
            'stats'        => $this->activity->stats(),
        ]);
    }
}
