<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
