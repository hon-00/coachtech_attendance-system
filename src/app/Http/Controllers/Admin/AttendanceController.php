<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Http\Requests\Admin\AdminUpdateAttendanceRequest;
use App\Models\AttendanceRequest;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $carbon = \Carbon\Carbon::parse($date);

        $prevDate = $carbon->copy()->subDay()->toDateString();
        $nextDate = $carbon->copy()->addDay()->toDateString();

        $attendances = \App\Models\Attendance::with('user')
            ->whereDate('work_date', $date)
            ->get();

        return view('admin.attendance.index', [
            'date' => $date,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'attendances' => $attendances,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakLogs')->findOrFail($id);
        $hasPendingRequest = $attendance->requests()
            ->where('status', AttendanceRequest::STATUS_PENDING)
            ->exists();

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'locked' => $hasPendingRequest,
        ]);
    }

    public function update(AdminUpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breakLogs')->findOrFail($id);

        if ($attendance->requests()->where('status', AttendanceRequest::STATUS_PENDING)->exists()) {
            return back()->withErrors(['locked' => '承認待ちのため修正はできません。']);
        }

        $workDate = $attendance->work_date;

        $attendance->clock_in  = $workDate . ' ' . $request->clock_in . ':00';
        $attendance->clock_out = $workDate . ' ' . $request->clock_out . ':00';
        $attendance->note      = $request->note;
        $attendance->save();

        $attendance->breakLogs()->delete();

        foreach ($request->breaks ?? [] as $b) {
            if (!empty($b['start']) && !empty($b['end'])) {
                $attendance->breakLogs()->create([
                    'break_start' => $workDate . ' ' . $b['start'] . ':00',
                    'break_end'   => $workDate . ' ' . $b['end']   . ':00',
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.show', ['id' => $attendance->id])
            ->with('success', '勤怠情報を更新した');
    }
}

