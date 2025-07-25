<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;

class StaffAttendanceController extends Controller
{
    public function index()
    {
        // role = 'staff' のユーザーを一覧取得
        $users = User::where('role', 'staff')->get();

        return view('admin.staff.index', compact('users'));
    }

    // ユーザーごとの勤怠一覧を表示
    public function show($id, Request $request)
    {
        $user = User::with(['attendances.breaks'])->findOrFail($id);

        // 月指定 (例: ?month=2025-07)
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now()->startOfMonth();

        $attendances = $user->attendances()
            ->whereMonth('date', $currentMonth->month)
            ->whereYear('date', $currentMonth->year)
            ->with('breaks')
            ->get();

        return view('admin.staff.attendances', compact('user', 'attendances', 'currentMonth'));
    }
}
