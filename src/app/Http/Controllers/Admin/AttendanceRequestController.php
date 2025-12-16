<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;

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
}
