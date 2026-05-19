<?php

namespace App\Services;

use App\Models\Visit;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class VisitAnalyticsService
{
    /**
     * @return array{total_visits: int, unique_visitors: int, top_city: string|null}
     */
    public function summary(int $days): array
    {
        $from = $this->since($days);

        $baseQuery = Visit::query()->since($from);

        $totalVisits = (clone $baseQuery)->count();
        $uniqueVisitors = (int) (clone $baseQuery)
            ->selectRaw('COUNT(DISTINCT visitor_id) as aggregate')
            ->value('aggregate');

        $topCity = Visit::query()
            ->since($from)
            ->select('city')
            ->selectRaw('COUNT(DISTINCT visitor_id) as unique_visits')
            ->groupBy('city')
            ->orderByDesc('unique_visits')
            ->value('city');

        return [
            'total_visits' => $totalVisits,
            'unique_visitors' => $uniqueVisitors,
            'top_city' => $topCity,
        ];
    }

    /**
     * @return array{labels: \Illuminate\Support\Collection, values: \Illuminate\Support\Collection}
     */
    public function hourlyUniqueVisits(int $days): array
    {
        $from = $this->since($days);
        $hourLabel = $this->hourBucketExpression();

        $rows = Visit::query()
            ->since($from)
            ->selectRaw("{$hourLabel} as hour_label")
            ->selectRaw('COUNT(DISTINCT visitor_id) as unique_visits')
            ->groupBy('hour_label')
            ->orderBy('hour_label')
            ->get();

        return [
            'labels' => $rows->pluck('hour_label'),
            'values' => $rows->pluck('unique_visits'),
        ];
    }

    /**
     * @return array{labels: \Illuminate\Support\Collection, values: \Illuminate\Support\Collection}
     */
    public function citiesUniqueVisits(int $days): array
    {
        $from = $this->since($days);

        $rows = Visit::query()
            ->since($from)
            ->select('city')
            ->selectRaw('COUNT(DISTINCT visitor_id) as unique_visits')
            ->groupBy('city')
            ->orderByDesc('unique_visits')
            ->get();

        return [
            'labels' => $rows->pluck('city'),
            'values' => $rows->pluck('unique_visits'),
        ];
    }

    private function since(int $days): CarbonInterface
    {
        return now()->subDays($days);
    }

    private function hourBucketExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m-%d %H:00', visited_at)",
            'mysql' => "DATE_FORMAT(visited_at, '%Y-%m-%d %H:00')",
            'pgsql' => "to_char(visited_at, 'YYYY-MM-DD HH24:00')",
            default => throw new InvalidArgumentException(
                'Unsupported database driver for hourly visit aggregation.'
            ),
        };
    }
}
