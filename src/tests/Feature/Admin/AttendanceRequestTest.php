<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function admin_can_view_all_pending_attendance_requests()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-01-10',
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'status'        => AttendanceRequest::STATUS_PENDING,
            'note'          => '修正理由',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('stamp_correction_request.index', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('修正理由');
    }

    /** @test */
    public function admin_can_view_all_approved_attendance_requests()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-01-10',
        ]);

        AttendanceRequest::factory()->create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'status'        => AttendanceRequest::STATUS_APPROVED,
            'note'          => '承認済み理由',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('stamp_correction_request.index', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済み理由');
    }

    /** @test */
    public function admin_can_view_attendance_request_detail()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => Carbon::parse('2025-01-10'),
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in'      => '09:00:00',
            'clock_out'     => '18:00:00',
            'note'          => '詳細確認用',
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->get(route('stamp_correction_request.show', $request));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('詳細確認用');
    }

    /** @test */
    public function admin_can_approve_attendance_request_and_update_attendance()
    {
        $admin = User::factory()->admin()->create();
        $user  = User::factory()->normal()->create();

        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'clock_in'  => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $request = AttendanceRequest::factory()->create([
            'user_id'       => $user->id,
            'attendance_id' => $attendance->id,
            'clock_in'      => '10:00:00',
            'clock_out'     => '18:00:00',
            'status'        => AttendanceRequest::STATUS_PENDING,
        ]);

        $response = $this
            ->actingAs($admin, 'admin')
            ->post(route('stamp_correction_request.approve', $request));

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_requests', [
            'id'     => $request->id,
            'status' => AttendanceRequest::STATUS_APPROVED,
        ]);

        $attendance->refresh();

        $this->assertSame(
            '10:00',
            Carbon::parse($attendance->clock_in)->format('H:i')
        );

        $this->assertSame(
            '18:00',
            Carbon::parse($attendance->clock_out)->format('H:i')
        );
    }
}