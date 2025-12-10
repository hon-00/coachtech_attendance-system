<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        \App\Models\User::factory(10)->create()->each(function ($user) {
            \App\Models\Attendance::factory(10)->create([
                'user_id' => $user->id,
            ])->each(function ($attendance) {
                \App\Models\BreakLog::factory(rand(1,3))->create([
                    'attendance_id' => $attendance->id,
                ]);
            });
        });
    }
}
