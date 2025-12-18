<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakLog;
use App\Models\AttendanceRequest;
use Carbon\Carbon;

class AttendanceController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->user();

        $month = $request->query('month', now()->format('Y-m'));
        $current = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $prevMonth = $current->copy()->subMonth()->format('Y-m');
        $nextMonth = $current->copy()->addMonth()->format('Y-m');

        $days = collect();
        for ($d = $current->copy(); $d->month === $current->month; $d->addDay()) {
            $days->push($d->copy());
        }

        $attendances = Attendance::with('breakLogs')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $current->year)
            ->whereMonth('work_date', $current->month)
            ->get();

        $attendancesByDate = $attendances->keyBy(fn ($a) => $a->work_date->toDateString());

        return view('attendance.index', [
            'days'              => $days,
            'attendancesByDate' => $attendancesByDate,
            'currentTime' => $current,
            'month'       => $current->format('Y-m'),
            'prevMonth'   => $prevMonth,
            'nextMonth'   => $nextMonth,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => Attendance::STATUS_NEW]
        );

        $lastBreak = $attendance->breakLogs()->latest()->first();// 未終了の休憩が存在すれば、画面再読み込み時も休憩中として扱う

        if ($lastBreak && !$lastBreak->break_end) {
            if ($attendance->status !== Attendance::STATUS_BREAK) {
                $attendance->status = Attendance::STATUS_BREAK;
                $attendance->save();
            }
        }

        return view('attendance.create', [
            'attendance' => $attendance,
            'currentTime' => now()
        ]);
    }

    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => Attendance::STATUS_NEW]
        );

        if ($attendance->clock_in) {
            return back()->with('error', '既に出勤済みです。');
        }

        $attendance->clock_in = now();
        $attendance->status = Attendance::STATUS_WORKING;
        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function clockOut(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('work_date', $today)
                                ->firstOrFail();

        if ($attendance->clock_out) {
            return back()->with('error', '既に退勤済みです。');
        }

        $attendance->clock_out = now();
        $attendance->status = Attendance::STATUS_LEAVE;
        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function breakIn(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('work_date', $today)
                                ->firstOrFail();

        if ($attendance->status !== Attendance::STATUS_WORKING) abort(403);

        BreakLog::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()
        ]);

        $attendance->status = Attendance::STATUS_BREAK;
        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function breakOut(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today)
            ->firstOrFail();

        if ($attendance->status !== Attendance::STATUS_BREAK) abort(403);

        $lastBreak = $attendance->breakLogs()
            ->whereNull('break_end')
            ->latest('id')
            ->firstOrFail();

        if ($lastBreak && !$lastBreak->break_end) {
            $end = now();
            if ($end->lt($lastBreak->break_start)) {
                abort(400, '休憩終了時刻が開始時刻より前です。');
            }

            $lastBreak->update(['break_end' => $end]);
        }

        $attendance->status = Attendance::STATUS_WORKING;
        $attendance->save();

        return redirect()->route('attendance.create');
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $date = $request->query('date', now()->toDateString());

        if ($id === 'new') {
            $date = $request->query('date');

            $attendance = Attendance::firstOrCreate(
                ['user_id' => $user->id, 'work_date' => $date],
                ['status' => Attendance::STATUS_NEW]
            );
            $isNew = true;
        } else {
            $attendance = Attendance::where('user_id', $user->id)
                ->with('breakLogs')
                ->findOrFail($id);
            $isNew = false;
        }

        $requestData = AttendanceRequest::where('attendance_id',  $attendance->id)
            ->latest()
            ->first();

        $hasOld = session()->hasOldInput();

        $useRequestView = $requestData
            && $requestData->status === AttendanceRequest::STATUS_PENDING
            && ! $hasOld;

        if ($useRequestView) {
            $attendance->clock_in  = $requestData->clock_in
                ? Carbon::parse($requestData->clock_in)
                : null;
            $attendance->clock_out = $requestData->clock_out
                ? Carbon::parse($requestData->clock_out)
                : null;
            $attendance->note = $requestData->note;

            $breakArray = $requestData->breaks ?? [];
            $attendance->setRelation('breakLogs', collect($breakArray)->map(function ($b) {
                return (object)[
                    'break_start' => !empty($b['start']) ? Carbon::parse($b['start']) : null,
                    'break_end'   => !empty($b['end'])   ? Carbon::parse($b['end'])   : null,
                ];
            }));
        }

        $editable = ! ($requestData && $requestData->status === AttendanceRequest::STATUS_PENDING) || $hasOld;

        return view('attendance.show', [
            'attendance'  => $attendance,
            'requestData' => $requestData,
            'editable'    => $editable,
            'isNew' => $isNew,
        ]);
    }
}
