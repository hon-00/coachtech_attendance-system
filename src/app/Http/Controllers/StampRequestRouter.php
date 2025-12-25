<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use App\Http\Controllers\AttendanceRequestController as WebAttendanceRequestController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;

class StampRequestRouter extends Controller
{
    public function index(Request $request)
    {
        if (auth()->guard('admin')->check()) {
            return app(AdminAttendanceRequestController::class)->index($request);
        }

        return app(WebAttendanceRequestController::class)->index($request);
    }

    public function show(Request $request, $id)
    {
        $attendanceRequest = AttendanceRequest::findOrFail($id);

        if (auth()->guard('admin')->check()) {
            return app(AdminAttendanceRequestController::class)
                ->show($attendanceRequest);
        }

        return app(WebAttendanceRequestController::class)
            ->show($attendanceRequest);
    }

    public function approve(Request $request, $id)
    {
        if (!auth()->guard('admin')->check()) {
            abort(403);
        }

        $attendanceRequest = AttendanceRequest::findOrFail($id);

        return app(AdminAttendanceRequestController::class)
            ->approve($attendanceRequest);
    }
}