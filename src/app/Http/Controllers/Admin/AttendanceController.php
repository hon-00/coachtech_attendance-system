<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Http\Requests\Admin\AdminUpdateAttendanceRequest;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $carbon = Carbon::parse($date);

        $prevDate = $carbon->copy()->subDay()->toDateString();
        $nextDate = $carbon->copy()->addDay()->toDateString();

        $attendances = Attendance::with('user')
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
        $attendance = Attendance::with('user', 'breakLogs','requests')->findOrFail($id);

        $pendingRequest = $attendance->requests()
            ->where('status', AttendanceRequest::STATUS_PENDING)
            ->first();

        $locked = $pendingRequest ? true : false;
        $isNew  = false;

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'locked' => $locked,
            'pendingRequest' => $pendingRequest,
            'isNew' => $isNew,
        ]);
    }

    public function showOrCreateByUserAndDate($userId, $date)
    {
        $workDate = Carbon::parse($date)->format('Y-m-d');

        $attendance = Attendance::firstOrNew([
            'user_id' => $userId,
            'work_date' => $workDate,
        ]);

        if (!$attendance->exists) {
            $attendance->user_id = $userId;
            $attendance->work_date = $date;
        }

        $pendingRequest = $attendance->exists
            ? $attendance->requests()->where('status', AttendanceRequest::STATUS_PENDING)->first()
            : null;

        $locked = $pendingRequest ? true : false;
        $isNew  = !$attendance->exists;

        return view('admin.attendance.show', compact('attendance', 'locked', 'pendingRequest', 'isNew'));
    }

    public function update(AdminUpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breakLogs')->findOrFail($id);

        if ($attendance->requests()->where('status', AttendanceRequest::STATUS_PENDING)->exists()) {
            return back()->withErrors(['locked' => '承認待ちのため修正はできません。']);
        }


        $dateOnly = Carbon::parse($attendance->work_date)->format('Y-m-d');

        $attendance->clock_in  = Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $request->clock_in);
        $attendance->clock_out = Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $request->clock_out);
        $attendance->note = $request->note;
        $attendance->save();

        $attendance->breakLogs()->delete();

        foreach ($request->breaks ?? [] as $b) {
            if (!empty($b['start']) && !empty($b['end'])) {
                $attendance->breakLogs()->create([
                    'break_start' => Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $b['start']),
                    'break_end'   => Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $b['end']),
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.show', ['attendance' => $attendance->id])
            ->with('success', '勤怠情報を更新しました');
    }

    public function store(AdminUpdateAttendanceRequest $request)
    {
        $dateOnly = Carbon::parse($request->work_date)->format('Y-m-d');

        $attendance = Attendance::create([
            'user_id'   => $request->user_id,
            'work_date' => $dateOnly,
            'clock_in'  => Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $request->clock_in),
            'clock_out' => Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $request->clock_out),
            'note'      => $request->note,
        ]);

        foreach ($request->breaks ?? [] as $b) {
            if (!empty($b['start']) && !empty($b['end'])) {
                $attendance->breakLogs()->create([
                    'break_start' => Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $b['start']),
                    'break_end'   => Carbon::createFromFormat('Y-m-d H:i', $dateOnly . ' ' . $b['end']),
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.show', ['attendance' => $attendance->id])
            ->with('success', '勤怠情報を作成した');
    }

    public function staffMonthly(Request $request, $id)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $prevMonth = $start->copy()->subMonth()->format('Y-m');
        $nextMonth = $start->copy()->addMonth()->format('Y-m');

        $period = CarbonPeriod::create($start, $end);

        $attendances = Attendance::with('breakLogs')
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy(fn($a) => $a->work_date->format('Y-m-d'));

        foreach ($period as $date) {
            $attendances[$date->format('Y-m-d')] ??= new Attendance([
                'work_date' => $date,
                'clock_in' => null,
                'clock_out' => null,
                'formatted_break_total' => null,
                'formatted_work_total' => null,
            ]);
        }

        return view('admin.user.attendance', [
            'user' => User::findOrFail($id),
            'period' => $period,
            'attendances' => $attendances,
            'month' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function exportCsv(Request $request, $id)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::with('breakLogs')
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->get();

        $filename = 'attendance_' . $id . '_' . $month . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計', '備考']);

            foreach ($attendances as $a) {
                fputcsv($handle, [
                    $a->work_date->format('Y-m-d'),
                    $a->clock_in?->format('H:i') ?? '',
                    $a->clock_out?->format('H:i') ?? '',
                    $a->formatted_break_total ?? '',
                    $a->formatted_work_total ?? '',
                    $a->note ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}