<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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

    public function index(Request $request){
        $userId = Auth::id();
        $tab = request()->get('tab', 'pending');

        $pendingRequests = AttendanceRequest::with(['attendance', 'user'])
        ->where('user_id', $userId)
        ->where('status', AttendanceRequest::STATUS_PENDING)
        ->orderBy('created_at', 'asc')
        ->get();

        $approvedRequests = AttendanceRequest::with(['attendance', 'user'])
        ->where('user_id', $userId)
        ->where('status', AttendanceRequest::STATUS_APPROVED)
        ->orderBy('created_at', 'asc')
        ->get();

        return view('attendance_request.index', [
            'tab'              => $tab,
            'pendingRequests'  => $pendingRequests,
            'approvedRequests' => $approvedRequests,
        ]);
    }

}