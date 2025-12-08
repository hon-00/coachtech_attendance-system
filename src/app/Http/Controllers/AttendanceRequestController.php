<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    public function store(AttendanceCorrectionRequest $request, $attendanceId)
    {
        $attendance = Attendance::findOrFail($attendanceId);

        $data = $request->validated();

        $breaks = $data['breaks'] ?? [];

        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => Auth::id(),

            'clock_in'      => $data['clock_in'] ?: null,
            'clock_out'     => $data['clock_out'] ?: null,
            'breaks'        => $breaks,
            'note'          => $data['note'],

            'status'        => AttendanceRequest::STATUS_PENDING,
        ]);

        $attendance->update(['status' => Attendance::STATUS_PENDING]);

        return redirect()
            ->route('attendance.detail', ['id' => $attendance->id])
            ->with('success', '修正申請を受け付けました。');
    }

}