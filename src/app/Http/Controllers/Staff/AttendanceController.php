<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        return view('user.attendance.create', compact('attendance'));
    }

    public function handleAction(Request $request)
    {
        $action = $request->input('action');
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $today],
            ['clock_in_time' => null]
        );

        switch ($action) {
            case 'start_work':
                if ($attendance->clock_in_time) {
                    return back()->withErrors(['出勤済みです']);
                }
                $attendance->clock_in_time = now()->toTimeString();
                $attendance->status = 'working';
                break;

            case 'start_break':
                if (!$attendance->clock_in_time || $attendance->clock_out_time) {
                    return back()->withErrors(['出勤中でないため休憩できません']);
                }
                $attendance->breaks()->create([
                    'break_start' => now()->toTimeString(),
                ]);
                $attendance->status = 'break';
                break;

            case 'resume_work':
                $lastBreak = $attendance->breaks()->latest()->first();
                if ($lastBreak && !$lastBreak->break_end) {
                    $lastBreak->break_end = now()->toTimeString();
                    $lastBreak->save();
                }
                $attendance->status = 'working';
                break;

            case 'end_work':
                if ($attendance->clock_out_time) {
                    return back()->withErrors(['すでに退勤済みです']);
                }
                $attendance->clock_out_time = now()->toTimeString();
                $attendance->status = 'done';
                break;

            default:
                return back()->withErrors(['不正な操作です']);
        }

        $attendance->save();
        return back()->with('success', '打刻が記録されました');
    }

    public function list(Request $request)
    {
        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now()->startOfMonth();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::with('breaks')
            ->where('user_id', Auth::id())
            ->whereBetween('date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->orderBy('date')
            ->get();

        return view('user.attendance.index', [
            'attendances' => $attendances,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['breaks', 'attendanceCorrections'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('user.attendance.show', compact('attendance'));
    }
}
