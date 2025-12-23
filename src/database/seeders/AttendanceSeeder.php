<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        User::factory(10)->create()->each(function ($user) {
            collect(range(0, 9))->each(function ($i) use ($user) {
                $attendance = Attendance::factory()->create([
                    'user_id'   => $user->id,
                    'work_date' => now()->subDays($i)->toDateString(),
                ]);

                BreakLog::factory(rand(1, 3))
                ->for($attendance)
                ->withinAttendance()
                ->create();
            });
        });
    }
}
