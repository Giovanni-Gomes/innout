<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WorkingHours;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagerialReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $year = $validated['year'] ?? (int) date('Y');
        $month = $validated['month'] ?? (int) date('n');
        $yearMonth = sprintf('%04d-%02d', $year, $month);

        $daily = WorkingHours::dailyWorkSeconds();
        $weekdays = countWeekdaysInMonth($yearMonth);
        $expectedSeconds = $weekdays * $daily;

        $employees = User::query()
            ->employees()
            ->whereNull('end_date')
            ->orderBy('name')
            ->get();

        $rows = [];
        foreach ($employees as $employee) {
            $total = WorkingHours::getWorkedTimeInMonth($employee->id, $yearMonth);
            $balance = $total - $expectedSeconds;
            $rows[] = [
                'user' => $employee,
                'total_seconds' => $total,
                'balance_seconds' => $balance,
            ];
        }

        $absentToday = WorkingHours::getAbsentUserNames(Carbon::today()->format('Y-m-d'));

        if ($request->query('export') === 'csv') {
            return $this->csvResponse($yearMonth, $rows, $expectedSeconds, $weekdays, $daily);
        }

        return view('admin.managerial_report', [
            'year' => $year,
            'month' => $month,
            'yearMonth' => $yearMonth,
            'rows' => $rows,
            'expectedSeconds' => $expectedSeconds,
            'weekdays' => $weekdays,
            'dailySeconds' => $daily,
            'absentToday' => $absentToday,
        ]);
    }

    /**
     * @param  array<int, array{user: User, total_seconds: int, balance_seconds: int}>  $rows
     */
    private function csvResponse(string $yearMonth, array $rows, int $expectedSeconds, int $weekdays, int $dailySeconds): StreamedResponse
    {
        $filename = 'relatorio_gerencial_'.$yearMonth.'.csv';

        return response()->streamDownload(function () use ($rows, $expectedSeconds, $weekdays, $dailySeconds) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Nome', 'E-mail', 'Horas trabalhadas', 'Segundos trabalhados', 'Esperado (seg)', 'Dias úteis', 'Saldo (seg)', 'Saldo (texto)']);
            foreach ($rows as $row) {
                $user = $row['user'];
                fputcsv($out, [
                    $user->name,
                    $user->email,
                    getTimeStringFromSeconds($row['total_seconds']),
                    $row['total_seconds'],
                    $expectedSeconds,
                    $weekdays,
                    $row['balance_seconds'],
                    getTimeStringFromSeconds(abs($row['balance_seconds'])),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
