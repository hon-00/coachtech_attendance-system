<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::today()->subDays(rand(0, 30));

        $clockIn = (clone $date)->setTime(8, 0)
            ->addMinutes(rand(0, 300));

        $clockOut = (clone $clockIn)->addMinutes(rand(360, 600));

        if ($clockOut->gt((clone $date)->setTime(18, 0))) {
            $clockOut = (clone $date)->setTime(18, 0);
        }

        return [
            'user_id'   => \App\Models\User::factory(),
            'work_date' => $date->toDateString(),
            'clock_in'  => $clockIn,
            'clock_out' => $clockOut,
            'status'    => rand(1, 3),
        ];
    }
}