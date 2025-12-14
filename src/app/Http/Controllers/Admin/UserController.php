<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('role', 0)->get();

        $currentMonth = now()->format('Y-m');

        return view('admin.user.index', [
            'users' => $users,
            'currentMonth' => $currentMonth,
        ]);
    }
}