<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $pendingRequests = AttendanceRequest::with('user', 'attendance')
            ->where('status', AttendanceRequest::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->get();

        $approvedRequests = AttendanceRequest::with('user', 'attendance')
            ->where('status', AttendanceRequest::STATUS_APPROVED)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.attendance_request.index', [
            'tab' => $tab,
            'pendingAdminRequests'  => $pendingRequests,
            'approvedAdminRequests' => $approvedRequests,
        ]);
    }

    public function show(AttendanceRequest $attendance_correct_request_id)
    {
        return view('admin.attendance_request.approve', [
            'attendanceRequest' => $attendance_correct_request_id
        ]);
    }

    public function approve(AttendanceRequest $attendance_correct_request_id)
    {
        $attendanceRequest = $attendance_correct_request_id;

        if ($attendanceRequest->status !== AttendanceRequest::STATUS_PENDING) {
            abort(404);
        }

        DB::transaction(function () use ($attendanceRequest) {
            $attendance = $attendanceRequest->attendance;

            $attendance->update([
                'clock_in'  => Carbon::parse($attendanceRequest->clock_in),
                'clock_out' => Carbon::parse($attendanceRequest->clock_out),
                'note'      => $attendanceRequest->note,
                'status'    => Attendance::STATUS_LEAVE,
            ]);

            $attendance->breakLogs()->delete();

            foreach ($attendanceRequest->breaks ?? [] as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $attendance->breakLogs()->create([
                        'break_start' => Carbon::parse($break['start']),
                        'break_end'   => Carbon::parse($break['end']),
                    ]);
                }
            }

            $attendanceRequest->update([
                'status' => AttendanceRequest::STATUS_APPROVED,
                'approved_at' => now(),
            ]);
        });

        return redirect()
            ->route('stamp_correction_request.index', ['tab' => 'approved'])
            ->with('success', '修正申請を承認しました。');
    }
}