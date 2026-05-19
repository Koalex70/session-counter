<?php

namespace App\Http\Controllers;

use App\Http\Requests\DashboardRequest;
use App\Services\VisitAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly VisitAnalyticsService $analytics,
    ) {}

    public function index(DashboardRequest $request): View
    {
        $days = $request->days();

        return view('dashboard.index', [
            'days' => $days,
            'summary' => $this->analytics->summary($days),
        ]);
    }

    public function hourly(DashboardRequest $request): JsonResponse
    {
        return response()->json(
            $this->analytics->hourlyUniqueVisits($request->days())
        );
    }

    public function cities(DashboardRequest $request): JsonResponse
    {
        return response()->json(
            $this->analytics->citiesUniqueVisits($request->days())
        );
    }
}
