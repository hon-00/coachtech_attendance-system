<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;
use Carbon\Carbon;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function admin_can_view_attendance_detail()
    {
        Carbon::setTestNow('2025-01-10');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'note' => '備考サンプル',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.show', ['attendance' => $attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('17:00');
        $response->assertSee('備考サンプル');
    }

    /** @test */
    public function cannot_save_attendance_if_break_start_after_clock_out()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'note' => '備考サンプル',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '09:00:00',
                'clock_out' => '17:00:00',
                'breaks' => [
                    ['start' => '18:00:00', 'end' => '18:30:00']
                ],
                'note' => '備考サンプル',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function cannot_save_attendance_if_break_end_after_clock_out()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'note' => '備考サンプル',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '09:00:00',
                'clock_out' => '17:00:00',
                'breaks' => [
                    ['start' => '12:00:00', 'end' => '18:00:00']
                ],
                'note' => '備考サンプル',
            ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function cannot_save_attendance_if_clock_in_after_clock_out()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'note' => '備考サンプル',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '18:00:00',
                'clock_out' => '17:00:00',
                'note' => '備考サンプル',
            ]);

        // ここは clock_out のバリデーションに合わせる
        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function cannot_save_attendance_if_note_is_empty()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-01-10',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
            'note' => '備考サンプル',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->put(route('admin.attendance.update', ['attendance' => $attendance->id]), [
                'clock_in' => '09:00:00',
                'clock_out' => '17:00:00',
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}