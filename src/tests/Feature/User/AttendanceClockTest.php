<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceClockTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function attendance_page_shows_current_datetime_and_status()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertViewIs('attendance.create');

        $now = Carbon::now();
        $response->assertSee($now->format('H:i'), false);
        $response->assertSee('勤務外');
    }

    /** @test */
    public function clock_in_updates_status_and_ui()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => Attendance::STATUS_WORKING,
        ]);

        $response->assertSee('出勤中');
        $attendance = Attendance::where('user_id', $user->id)->first();
        $response->assertSee($attendance->clock_in->format('H:i'), false);
    }

    /** @test */
    public function break_in_and_break_out_updates_status_and_ui()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        $this->actingAs($user)->post('/attendance/break-in');
        $response = $this->actingAs($user)->get('/attendance');
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => Attendance::STATUS_BREAK,
        ]);
        $response->assertSee('休憩中');

        $this->actingAs($user)->post('/attendance/break-out');
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function clock_out_updates_status_and_ui()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status'  => Attendance::STATUS_LEAVE,
        ]);

        $response->assertSee('退勤済');
        $attendance = Attendance::where('user_id', $user->id)->first();
        $response->assertSee($attendance->clock_out->format('H:i'), false);
    }

    /** @test */
    public function authenticated_user_can_view_attendance_page()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertViewIs('attendance.create');
    }

    /** @test */
    public function guest_cannot_view_attendance_page()
    {
        $response = $this->get('/attendance');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_clock_in()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_WORKING,
        ]);
    }

    /** @test */
    public function guest_cannot_clock_in()
    {
        $response = $this->post('/attendance/clock-in');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function authenticated_user_can_clock_out()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');
        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $response->assertRedirect();
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => Attendance::STATUS_LEAVE,
        ]);
    }

    /** @test */
    public function authenticated_user_can_break_in_and_out()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');

        $this->assertDatabaseCount('break_logs', 1);
    }

    /** @test */
    public function cannot_clock_in_twice_in_one_day()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');
        $this->actingAs($user)->post('/attendance/clock-in');

        $this->assertDatabaseCount('attendances', 1);
    }

    /** @test */
    public function can_take_multiple_breaks()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/attendance/clock-in');

        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');

        $this->actingAs($user)->post('/attendance/break-in');
        $this->actingAs($user)->post('/attendance/break-out');

        $this->assertDatabaseCount('break_logs', 2);
    }
}