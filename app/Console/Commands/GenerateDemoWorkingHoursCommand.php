<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkingHours;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDemoWorkingHoursCommand extends Command
{
    protected $signature = 'innout:demo-data';

    protected $description = 'Gera registos de ponto fictícios para funcionários (apenas ambiente local).';

    public function handle(): int
    {
        if (! app()->isLocal()) {
            $this->error('Este comando só pode ser executado com APP_ENV=local.');

            return self::FAILURE;
        }

        $employees = User::query()->where('is_admin', false)->orderBy('id')->get();
        if ($employees->isEmpty()) {
            $this->error('Nenhum funcionário encontrado. Execute: php artisan migrate --seed');

            return self::FAILURE;
        }

        $configs = [
            ['start' => date('Y-m-01'), 'regular' => 70, 'extra' => 20, 'lazy' => 10],
            ['start' => date('Y-m-d', strtotime('first day of last month')), 'regular' => 20, 'extra' => 75, 'lazy' => 5],
            ['start' => date('Y-m-d', strtotime('first day of last month')), 'regular' => 20, 'extra' => 10, 'lazy' => 70],
        ];

        DB::transaction(function () use ($employees, $configs): void {
            WorkingHours::query()->delete();

            foreach ($employees as $index => $user) {
                $cfg = $configs[$index % count($configs)];
                $this->populateWorkingHours(
                    (int) $user->id,
                    $cfg['start'],
                    $cfg['regular'],
                    $cfg['extra'],
                    $cfg['lazy']
                );
            }
        });

        $this->info('Dados de demonstração gerados para '.$employees->count().' funcionário(s).');

        return self::SUCCESS;
    }

    private function getDayTemplateByOdds(int $regularRate, int $extraRate, int $lazyRate): array
    {
        $regularDayTemplate = [
            'time1' => '08:00:00',
            'time2' => '12:00:00',
            'time3' => '13:00:00',
            'time4' => '17:00:00',
            'worked_time' => WorkingHours::dailyWorkSeconds(),
        ];

        $extraHourDayTemplate = [
            'time1' => '08:00:00',
            'time2' => '12:00:00',
            'time3' => '13:00:00',
            'time4' => '18:00:00',
            'worked_time' => WorkingHours::dailyWorkSeconds() + 3600,
        ];

        $lazyDayTemplate = [
            'time1' => '08:30:00',
            'time2' => '12:00:00',
            'time3' => '13:00:00',
            'time4' => '17:00:00',
            'worked_time' => WorkingHours::dailyWorkSeconds() - 1800,
        ];

        $value = random_int(0, 100);
        if ($value <= $regularRate) {
            return $regularDayTemplate;
        }
        if ($value <= $regularRate + $extraRate) {
            return $extraHourDayTemplate;
        }

        return $lazyDayTemplate;
    }

    private function populateWorkingHours(int $userId, string $initialDate, int $regularRate, int $extraRate, int $lazyRate): void
    {
        $currentDate = $initialDate;
        $yesterday = new DateTime();
        $yesterday->modify('-1 day');
        $columns = ['user_id' => $userId, 'work_date' => $currentDate];

        while (isBefore($currentDate, $yesterday)) {
            if (! isWeekend($currentDate)) {
                $template = $this->getDayTemplateByOdds($regularRate, $extraRate, $lazyRate);
                $columns = array_merge($columns, $template);
                (new WorkingHours($columns))->save();
            }
            $currentDate = getNextDay($currentDate)->format('Y-m-d');
            $columns['work_date'] = $currentDate;
        }
    }
}
