<?php

namespace App\Http\Controllers;

use App\Models\WorkingHours;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MonthlyReport extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $year = $validated['year'] ?? (int) date('Y');
        $month = $validated['month'] ?? (int) date('n');
        $yearMonth = sprintf('%04d-%02d', $year, $month);

        $userId = (int) $request->user()->id;
        $registries = WorkingHours::getMonthlyReport($userId, $yearMonth);
        $daily = WorkingHours::dailyWorkSeconds();

        $first = getFirstDayOfMonth($yearMonth);
        $last = getLastDayOfMonth($yearMonth);

        $days = [];
        $totalWorked = 0;
        for ($d = clone $first; $d <= $last; $d->modify('+1 day')) {
            $key = $d->format('Y-m-d');
            $wh = $registries[$key] ?? null;
            if ($wh !== null && $wh->worked_time !== null) {
                $totalWorked += (int) $wh->worked_time;
            }
            $days[] = [
                'date' => $key,
                'is_weekend' => isWeekend($d),
                'working_hours' => $wh,
            ];
        }

        $weekdays = countWeekdaysInMonth($yearMonth);
        $expectedSeconds = $weekdays * $daily;
        $balanceSeconds = $totalWorked - $expectedSeconds;

        return view('monthly_report', [
            'year' => $year,
            'month' => $month,
            'yearMonth' => $yearMonth,
            'days' => $days,
            'totalWorked' => $totalWorked,
            'expectedSeconds' => $expectedSeconds,
            'balanceSeconds' => $balanceSeconds,
            'weekdays' => $weekdays,
            'dailySeconds' => $daily,
        ]);
    }
}
