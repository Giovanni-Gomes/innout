<?php

namespace Tests\Feature;

use App\Models\WorkingHours;
use Tests\TestCase;

class WorkingHoursExitTimeTest extends TestCase
{
    public function test_exit_time_respects_configured_daily_work_seconds(): void
    {
        config()->set('innout.daily_work_seconds', 7 * 60 * 60);

        $workingHours = new WorkingHours([
            'time1' => '08:00:00',
        ]);

        $this->assertSame('15:00:00', $workingHours->getExitTime()->format('H:i:s'));
    }
}
