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

    public function create(Request $request)
    {
        $user = User::findOrFail($request->query('user_id'));
        $date = $request->query('date', now()->toDateString());

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'work_date' => $date,
        ]);

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'locked' => false,
            'pendingRequest' => null,
            'isNew' => true,
        ]);
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
            ->with('success', '勤怠情報を作成しました');
    }

    public function staffMonthly(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $date = $request->query('date', now()->toDateString());

        $attendance = Attendance::firstOrNew([
            'user_id' => $user->id,
            'work_date' => $date,
        ]);

        $month = $request->query('month', now()->format('Y-m'));
        $period = \Carbon\Carbon::parse($month)->daysUntil(\Carbon\Carbon::parse($month)->endOfMonth());
        $prevMonth = Carbon::parse($month . '-01')->subMonth()->format('Y-m');
        $nextMonth = Carbon::parse($month . '-01')->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('work_date', \Carbon\Carbon::parse($month)->month)
            ->get()
            ->keyBy(fn($a) => $a->work_date->toDateString());

        return view('admin.user.attendance', compact('user', 'attendance', 'period', 'attendances', 'month', 'prevMonth', 'nextMonth'));
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

        $callback = function () use ($attendances) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計', '備考']);

            foreach ($attendances as $attendance) {
                fputcsv($handle, [
                    $attendance->work_date->format('Y-m-d'),
                    $attendance->clock_in?->format('H:i') ?? '',
                    $attendance->clock_out?->format('H:i') ?? '',
                    $attendance->formatted_break_total ?? '',
                    $attendance->formatted_work_total ?? '',
                    $attendance->note ?? '',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}