<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\ActivityLogRepository;
use App\Repositories\StudentRepository;
use App\Support\Auth;
use App\Support\View;

final class DashboardController
{
    public function __construct(
        private readonly StudentRepository $students,
        private readonly ActivityLogRepository $activity,
    ) {
    }

    public function index(): void
    {
        Auth::require();

        View::render('dashboard', [
            'pageTitle'     => 'Dashboard',
            'pageSubtitle'  => 'Overview of student records and enrolment',
            'stats'         => $this->students->stats(),
            'byCourse'      => $this->students->countByCourse(),
            'byYearLevel'   => $this->students->countByYearLevel(),
            'recent'        => $this->students->recent(5),
            'activityStats' => $this->activity->stats(),
        ]);
    }
}
