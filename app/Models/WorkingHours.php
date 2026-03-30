<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\AppException;
use DateInterval;
use DateTime;
use DateTimeImmutable;
class WorkingHours extends Model
{
    use HasFactory;

    protected $table = 'working_hours';
    protected $fillable = [
        'user_id',
        'work_date',
        'time1',
        'time2',
        'time3',
        'time4',
        'worked_time'
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function dailyWorkSeconds(): int
    {
        return (int) config('innout.daily_work_seconds', 60 * 60 * 8);
    }

    public static function loadFromUserAndDate($userId, $workDate) {
        $registry = WorkingHours::firstOrNew(
            ['user_id' => $userId,
            'work_date' => $workDate]
        );

        return $registry;
    }

    public function getNextTime() {
        if(!$this->time1) return 'time1';
        if(!$this->time2) return 'time2';
        if(!$this->time3) return 'time3';
        if(!$this->time4) return 'time4';
        return null;
    }

    public function getActiveClock() {
        $nextTime = $this->getNextTime();
        if($nextTime === 'time1' || $nextTime === 'time3') {
            return 'exitTime';
        } elseif($nextTime === 'time2' || $nextTime === 'time4') {
            return 'workedInterval';
        } else {
            return null;
        }
    }

    public function innout($time) {
        $timeColumn = $this->getNextTime();
        if(!$timeColumn) {
            throw new AppException("Você já fez os 4 batimentos do dia!");
        }

        $this->$timeColumn = $time;
        $this->worked_time = getSecondsFromDateInterval($this->getWorkedInterval());
        if($this->id) {

            $this->update();
        } else {
            $this->save();
        }
    }

    function getWorkedInterval() {
        [$t1, $t2, $t3, $t4] = $this->getTimes();

        $part1 = new DateInterval('PT0S');
        $part2 = new DateInterval('PT0S');

        if($t1) $part1 = $t1->diff(new DateTime());
        if($t2) $part1 = $t1->diff($t2);
        if($t3) $part2 = $t3->diff(new DateTime());
        if($t4) $part2 = $t3->diff($t4);

        return sumIntervals($part1, $part2);
    }

    function getLunchInterval() {
        [, $t2, $t3,] = $this->getTimes();
        $lunchInterval = new DateInterval('PT0S');

        if($t2) $lunchInterval = $t2->diff(new DateTime());
        if($t3) $lunchInterval = $t2->diff($t3);

        return $lunchInterval;
    }

    function getExitTime() {
        [$t1,,, $t4] = $this->getTimes();
        $workDay = DateInterval::createFromDateString('8 hours');

        if(!$t1) {
            return (new DateTimeImmutable())->add($workDay);
        } elseif($t4) {
            return $t4;
        } else {
            $total = sumIntervals($workDay, $this->getLunchInterval());
            return $t1->add($total);
        }
    }

    function getBalance() {
        $daily = static::dailyWorkSeconds();
        if(!$this->time1 && !isPastWorkday($this->work_date)) return '';
        if((int) $this->worked_time === $daily) return '-';

        $balance = (int) $this->worked_time - $daily;
        $balanceString = getTimeStringFromSeconds(abs($balance));
        $sign = (int) $this->worked_time >= $daily ? '+' : '-';
        return "{$sign}{$balanceString}";
    }

    /**
     * Nomes de utilizadores ativos (não admin) sem batimento no dia indicado.
     *
     * @return array<int, string>
     */
    public static function getAbsentUserNames(?string $workDate = null): array
    {
        $date = $workDate ?? (new DateTime())->format('Y-m-d');

        return User::query()
            ->employees()
            ->whereNull('end_date')
            ->whereNotIn('id', function ($q) use ($date) {
                $q->select('user_id')
                    ->from('working_hours')
                    ->where('work_date', $date)
                    ->whereNotNull('time1');
            })
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    public static function getWorkedTimeInMonth(int $userId, string $yearAndMonth): int
    {
        $startDate = (new DateTime("{$yearAndMonth}-1"))->format('Y-m-d');
        $endDate = getLastDayOfMonth($yearAndMonth)->format('Y-m-d');

        return (int) static::query()
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->sum('worked_time');
    }

    /**
     * Registos do mês indexados por data (Y-m-d).
     *
     * @return array<string, WorkingHours>
     */
    public static function getMonthlyReport(int $userId, $date): array
    {
        $startDate = getFirstDayOfMonth($date)->format('Y-m-d');
        $endDate = getLastDayOfMonth($date)->format('Y-m-d');

        $rows = static::query()
            ->where('user_id', $userId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        $registries = [];
        foreach ($rows as $row) {
            $registries[$row->work_date->format('Y-m-d')] = $row;
        }

        return $registries;
    }

    private function getTimes() {
        $times = [];

        $this->time1 ? array_push($times, getDateFromString($this->time1)) : array_push($times, null);
        $this->time2 ? array_push($times, getDateFromString($this->time2)) : array_push($times, null);
        $this->time3 ? array_push($times, getDateFromString($this->time3)) : array_push($times, null);
        $this->time4 ? array_push($times, getDateFromString($this->time4)) : array_push($times, null);

        return $times;
    }
}
