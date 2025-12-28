<?php

namespace Tests\Feature;

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
    public function authenticated_user_can_view_own_attendance_list()
    {
        $user = User::factory()->create();

        $today = Carbon::now()->format('Y-m-d');

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $today,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertViewIs('attendance.index');
        $response->assertSee(Carbon::now()->format('m/d'));
    }

    /** @test */
    public function attendance_list_displays_current_month_by_default()
    {
        $user = User::factory()->create();

        $today = Carbon::now()->format('Y-m-d');

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $today,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(Carbon::now()->format('Y/m'));
    }

    /** @test */
    public function attendance_list_can_display_previous_month()
    {
        $user = User::factory()->create();

        $prevMonth = Carbon::now()->subMonth();
        $date = $prevMonth->format('Y-m-d');

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $date,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $prevMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($prevMonth->format('m/d'));
    }

    /** @test */
    public function attendance_list_can_display_next_month()
    {
        $user = User::factory()->create();

        $nextMonth = Carbon::now()->addMonth();
        $date = $nextMonth->format('Y-m-d');

        Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $date,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('m/d'));
    }

    /** @test */
    public function attendance_list_detail_link_navigates_to_detail_page()
    {
        $user = User::factory()->create();

        $today = Carbon::now()->format('Y-m-d');

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $today,
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list');

        $response->assertSee("/attendance/detail/{$attendance->id}", false);

        $this->actingAs($user)
            ->get("/attendance/detail/{$attendance->id}")
            ->assertStatus(200);
    }

    /** @test */
    public function guest_cannot_view_attendance_list()
    {
        $response = $this->get('/attendance/list');

        $response->assertRedirect('/login');
    }
}