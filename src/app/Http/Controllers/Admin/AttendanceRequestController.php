<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');

        $pendingAdminRequests = AttendanceRequest::with('user', 'attendance')
            ->where('status', AttendanceRequest::STATUS_PENDING)
            ->orderBy('created_at', 'asc')
            ->get();

        $approvedAdminRequests = AttendanceRequest::with('user', 'attendance')
            ->where('status', AttendanceRequest::STATUS_APPROVED)
            ->orderBy('created_at', 'asc')
            ->get();

        return view(
            'admin.attendance_request.index',
            compact('pendingAdminRequests', 'approvedAdminRequests', 'tab')
        );
    }

    public function show(AttendanceRequest $attendance_correct_request)
    {
        return view(
            'admin.attendance_request.approve',
            [
                'attendanceRequest' => $attendance_correct_request
            ]
        );
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $request = AttendanceRequest::with('attendance')
                ->where('id', $id)
                ->where('status', AttendanceRequest::STATUS_PENDING)
                ->firstOrFail();

            $attendance = $request->attendance;

            $date = $attendance->work_date->format('Y-m-d');

            $attendance->update([
                'clock_in'  => Carbon::parse($request->clock_in),
                'clock_out' => Carbon::parse($request->clock_out),
                'note'      => $request->note,
            ]);

            $attendance->breakLogs()->delete();

            foreach ($request->breaks ?? [] as $break) {
                if (!empty($break['break_start']) && !empty($break['break_end'])) {
                    $attendance->breakLogs()->create([
                        'break_start' => Carbon::parse($break['break_start']),
                        'break_end'   => Carbon::parse($break['break_end']),
                    ]);
                }
            }

            $request->update([
                'status' => AttendanceRequest::STATUS_APPROVED,
                'approved_at' => now(),
            ]);
        });

        return redirect()
            ->back()
            ->with('success', '修正申請を承認しました。');
    }
}
