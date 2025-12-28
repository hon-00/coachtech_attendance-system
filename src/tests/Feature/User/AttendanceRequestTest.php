<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceRequestFullTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    /** @test */
    public function attendance_detail_displays_correctly_with_breaks_and_request_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendance->breakLogs()->createMany([
            ['break_start' => '12:00', 'break_end' => '12:30'],
            ['break_start' => '15:00', 'break_end' => '15:15'],
        ]);

        // 申請なし → 編集可能
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee('name="clock_in"', false);
        $response->assertSee('name="clock_out"', false);
        $response->assertSee('name="breaks[0][start]"', false);
        $response->assertSee('name="breaks[0][end]"', false);
        $response->assertSee('name="breaks[1][start]"', false);
        $response->assertSee('name="breaks[1][end]"', false);
        $response->assertSee('name="breaks[new][start]"', false);
        $response->assertSee('name="breaks[new][end]"', false);
        $response->assertDontSee('*承認待ちのため修正できません');

        // 修正申請作成（休憩含む）
        AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => '10:00',
            'clock_out' => '19:00',
            'breaks' => [
                ['start' => '12:30', 'end' => '13:00'],
                ['start' => '15:30', 'end' => '15:45'],
            ],
            'note' => 'テスト休憩',
            'status' => AttendanceRequest::STATUS_PENDING,
        ]);

        // 申請中 → 編集不可
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertDontSee('name="clock_in"');
        $response->assertDontSee('name="clock_out"');
        $response->assertDontSee('name="breaks[0][start]"');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('承認待ちのため修正できません', true);
    }

    /** @test */
    public function submitting_attendance_request_with_breaks_updates_status_and_database()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $attendance->breakLogs()->create([
            'break_start' => '12:00',
            'break_end'   => '12:30',
        ]);

        $data = [
            'clock_in' => '09:30',
            'clock_out' => '18:30',
            'breaks' => [
                ['start' => '12:15', 'end' => '12:45'],
                'new' => ['start' => '15:00', 'end' => '15:30'],
            ],
            'note' => '遅刻修正 + 休憩追加',
        ];

        $response = $this->post(route('attendance.request.store', ['attendanceId' => $attendance->id]), $data);

        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));

        $this->assertDatabaseHas('attendance_requests', [
            'user_id' => $user->id,
            'note' => '遅刻修正 + 休憩追加',
            'status' => AttendanceRequest::STATUS_PENDING,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => Attendance::STATUS_PENDING,
        ]);

        $request = AttendanceRequest::latest()->first();
        $this->assertCount(2, $request->breaks); // 既存修正 + 新規
        $this->assertEquals('12:15', $request->breaks[0]['start']);
        $this->assertEquals('12:45', $request->breaks[0]['end']);
        $this->assertEquals('15:00', $request->breaks['new']['start']);
        $this->assertEquals('15:30', $request->breaks['new']['end']);
    }

    /** @test */
    public function submitting_invalid_break_time_fails_validation()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $data = [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                0 => ['start' => '08:00', 'end' => '08:30'],
                'new' => ['start' => '19:00', 'end' => '19:30'],
            ],
            'note' => '不正休憩',
        ];

        $response = $this->post(route('attendance.request.store', ['attendanceId' => $attendance->id]), $data);

        $response->assertSessionHasErrors([
            'breaks.0.start',
            'breaks.0.end',
            'breaks.new.start',
            'breaks.new.end',
        ]);
    }
}