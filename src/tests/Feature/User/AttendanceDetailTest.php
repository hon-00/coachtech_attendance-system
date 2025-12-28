<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakLog;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function authenticated_user_can_view_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertViewIs('attendance.show');
        $response->assertViewHas('attendance');
    }

    /** @test */
    public function guest_cannot_view_attendance_detail()
    {
        $attendance = Attendance::factory()->create();

        $response = $this->get("/attendance/detail/{$attendance->id}");

        $response->assertRedirect('/login');
    }

    /** @test */
    public function attendance_detail_displays_logged_in_users_data_correctly()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-01-01',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('2025年');
        $response->assertSee('01月01日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function attendance_detail_displays_break_times_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-01-01',
            'clock_in'  => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        BreakLog::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start'   => '12:00:00',
            'break_end'     => '13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}