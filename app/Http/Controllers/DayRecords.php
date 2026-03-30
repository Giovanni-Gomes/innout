<?php

namespace App\Http\Controllers;

use App\Exceptions\AppException;
use App\Models\WorkingHours;
use Illuminate\Support\Carbon;

class DayRecords extends Controller
{
    public function index()
    {
        $today = Carbon::now()
            ->locale(app()->getLocale())
            ->isoFormat('D [de] MMMM [de] YYYY');
        $workingHours = WorkingHours::loadFromUserAndDate(auth()->user()->id, date('Y-m-d'));

        return view('day_records', [
            'today' => $today,
            'workingHours' => $workingHours,
            'allowDevTimeSimulation' => app()->isLocal(),
        ]);
    }

    public function point()
    {
        $workingHours = WorkingHours::loadFromUserAndDate(auth()->user()->id, date('Y-m-d'));
        try {
            $currentTime = Carbon::now()->format('H:i:s');
            $forcedTime = request('forcedTime');
            if ($forcedTime !== null && $forcedTime !== '' && app()->isLocal()) {
                $currentTime = $forcedTime;
            }
            $workingHours->innout($currentTime);
            $type = 'success';
            $msg = 'Ponto inserido com sucesso!';
        } catch (AppException $e) {
            $type = 'danger';
            $msg = $e->getMessage();
        }
        return to_route('dashboard', ['workingHours' => $workingHours])->with($type, $msg);
    }


}
