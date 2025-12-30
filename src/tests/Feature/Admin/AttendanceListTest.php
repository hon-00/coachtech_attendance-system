<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function admin_can_view_all_users_attendance_for_today()
    {
        Carbon::setTestNow('2025-01-10');

        $admin = User::factory()->admin()->create();
        $user  = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-01-10',
            'clock_in'  => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertStatus(200);

        $response->assertSee('2025年01月10日の勤怠');
        $response->assertSee('2025/01/10');
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('17:00');

        $response->assertSee($attendance->formatted_break_total);
        $response->assertSee($attendance->formatted_work_total);
    }

    /** @test */
    public function previous_day_attendance_is_displayed()
    {
        Carbon::setTestNow('2025-01-10');

        $admin = User::factory()->admin()->create();
        $user  = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-01-09',
            'clock_in'  => '09:30:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2025-01-09');

        $response->assertStatus(200);
        $response->assertSee('2025年01月09日の勤怠');
        $response->assertSee('2025/01/09');
        $response->assertSee($user->name);
        $response->assertSee('09:30');
        $response->assertSee('18:00');
        $response->assertSee($attendance->formatted_break_total);
        $response->assertSee($attendance->formatted_work_total);
    }

    /** @test */
    public function next_day_attendance_is_displayed()
    {
        Carbon::setTestNow('2025-01-10');

        $admin = User::factory()->admin()->create();
        $user  = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-01-11',
            'clock_in'  => '08:45:00',
            'clock_out' => '17:15:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=2025-01-11');

        $response->assertStatus(200);
        $response->assertSee('2025年01月11日の勤怠');
        $response->assertSee('2025/01/11');
        $response->assertSee($user->name);
        $response->assertSee('08:45');
        $response->assertSee('17:15');
        $response->assertSee($attendance->formatted_break_total);
        $response->assertSee($attendance->formatted_work_total);
    }

    /** @test */
    public function non_admin_cannot_view_attendance_list()
    {
        $user = User::factory()->normal()->create();

        $response = $this
            ->actingAs($user, 'web')
            ->get('/admin/attendance/list');

        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }
}