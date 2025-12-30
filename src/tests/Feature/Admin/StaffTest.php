<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function admin_can_view_staff_list()
    {
        $admin = User::factory()->admin()->create();
        $user1 = User::factory()->normal()->create();
        $user2 = User::factory()->normal()->create();

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.user.index'));

        $response->assertStatus(200);
        $response->assertSee($user1->name);
        $response->assertSee($user1->email);
        $response->assertSee($user2->name);
        $response->assertSee($user2->email);

        $response->assertSee(route('admin.attendance.staff', ['id' => $user1->id]));
        $response->assertSee(route('admin.attendance.staff', ['id' => $user2->id]));
    }

    /** @test */
    public function admin_can_view_individual_user_attendance()
    {
        Carbon::setTestNow('2025-12-29');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-12-28',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $attendance->breakLogs()->create([
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $prevMonth = '2025-11';
        $nextMonth = '2026-01';
        $month = '2025-12';

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', ['id' => $user->id, 'month' => $month]));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('17:00');
        $response->assertSee($attendance->formatted_break_total);
        $response->assertSee($attendance->formatted_work_total);

        $response->assertSee(route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]));
        $response->assertSee(route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]));

        $response->assertSee(route('admin.attendance.show', ['attendance' => $attendance->id]));
    }

    /** @test */
    public function previous_month_and_next_month_attendance_are_displayed_correctly()
    {
        Carbon::setTestNow('2025-12-29');

        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();

        $attendancePrev = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-11-15',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $attendanceNext = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2026-01-10',
            'clock_in' => '10:00:00',
            'clock_out' => '18:00:00',
        ]);

        $responsePrev = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', ['id' => $user->id, 'month' => '2025-11']));
        $responsePrev->assertStatus(200);
        $responsePrev->assertSee('11/15');
        $responsePrev->assertSee('09:00');
        $responsePrev->assertSee('17:00');

        $responseNext = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', ['id' => $user->id, 'month' => '2026-01']));
        $responseNext->assertStatus(200);
        $responseNext->assertSee('01/10');
        $responseNext->assertSee('10:00');
        $responseNext->assertSee('18:00');
    }

    /** @test */
    public function clicking_detail_link_redirects_to_attendance_detail()
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->normal()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-12-29',
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('admin.attendance.staff', ['id' => $user->id]));

        $response->assertSee(route('admin.attendance.show', ['attendance' => $attendance->id]));
    }

    /** @test */
    public function non_admin_cannot_access_staff_list_or_attendance()
    {
        $user = User::factory()->normal()->create();

        $response1 = $this->actingAs($user, 'web')->get(route('admin.user.index'));
        $response1->assertStatus(302);
        $response1->assertRedirect('/admin/login');

        $response2 = $this->actingAs($user, 'web')->get(route('admin.attendance.staff', ['id' => $user->id]));
        $response2->assertStatus(302);
        $response2->assertRedirect('/admin/login');
    }
}