<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakLog;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => Attendance::STATUS_NEW]
        );

        $lastBreak = $attendance->breakLogs()->latest()->first();

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

    // 出勤処理
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
        $attendance->status = Attendance::STATUS_WORKING; // 出勤中
        $attendance->save();

        return redirect()->route('attendance.index');
    }

    // 退勤処理
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
        $attendance->status = Attendance::STATUS_LEAVE; // 退勤済
        $attendance->save();

        return redirect()->route('attendance.create');
    }

    // 休憩入
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

        $attendance->status = Attendance::STATUS_BREAK; // 休憩中
        $attendance->save();

        return redirect()->route('attendance.create');
    }

    // 休憩戻
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

        $attendance->status = Attendance::STATUS_WORKING; // 出勤中に戻す
        $attendance->save();

        return redirect()->route('attendance.create');
    }
}
