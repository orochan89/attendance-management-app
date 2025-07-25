<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\CarbonPeriod;


class AttendanceController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if (!$attendance) {
            $attendance = new Attendance(['status' => 'off']);
        }

        return view('user.attendance.create', compact('attendance'));
    }

    public function handleAction(Request $request)
    {
        $action = $request->input('action');
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $userId, 'date' => $today],
        );

        switch ($action) {
            case 'start_work':
                if ($attendance->clock_in_time) {
                    return back()->withErrors(['出勤済みです']);
                }
                $attendance->clock_in_time = now();
                $attendance->status = 'working';
                break;

            case 'start_break':
                if (!$attendance->clock_in_time || $attendance->clock_out_time) {
                    return back()->withErrors(['出勤中でないため休憩できません']);
                }
                $attendance->breaks()->create([
                    'break_start' => now(),
                ]);
                $attendance->status = 'break';
                break;

            case 'resume_work':
                $lastBreak = $attendance->breaks()->latest()->first();
                if ($lastBreak && !$lastBreak->break_end) {
                    $lastBreak->break_end = now();
                    $lastBreak->save();
                }
                $attendance->status = 'working';
                break;

            case 'end_work':
                if ($attendance->clock_out_time) {
                    return back()->withErrors(['すでに退勤済みです']);
                }
                $attendance->clock_out_time = now();
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

        $dates = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendanceList = collect();

        foreach ($dates as $date) {
            $attendance = Attendance::firstOrCreate([
                'user_id' => Auth::id(),
                'date' => $date->toDateString(),
            ]);
            $attendanceList->put($date->format('Y-m-d'), $attendance);
        }

        return view('user.attendance.index', [
            'attendances' => $attendanceList,
            'dates' => $dates,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        if ($attendance->user_id !== Auth::id()) {
            abort(403, 'この勤怠情報にアクセスする権限がありません。');
        }

        // 最新の修正申請とその休憩修正を取得
        $correction = $attendance->attendanceCorrections()
            ->with('breakCorrections')
            ->latest()
            ->first();

        $breaks = $attendance->breaks;

        return view('user.attendance.show', compact('attendance', 'correction', 'breaks'));
    }
}
