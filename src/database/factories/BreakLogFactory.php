<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class BreakLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => null,
            'break_start'   => null,
            'break_end'     => null,
        ];
    }

    public function withinAttendance()
    {
        return $this->afterMaking(function ($breakLog) {
            $attendance = $breakLog->attendance;

            if (!$attendance) {
                return;
            }

            $clockIn  = Carbon::parse($attendance->clock_in);
            $clockOut = Carbon::parse($attendance->clock_out);

            $range = $clockOut->diffInMinutes($clockIn);

            if ($range < 120) {
                return;
            }

            $breakStart = (clone $clockIn)
                ->addMinutes(rand(60, $range - 60));

            $breakEnd = (clone $breakStart)
                ->addMinutes(rand(30, 60));

            if ($breakEnd->gte($clockOut)) {
                $breakEnd = (clone $clockOut)->subMinute();
            }

            $breakLog->break_start = $breakStart;
            $breakLog->break_end   = $breakEnd;
        });
    }
}
