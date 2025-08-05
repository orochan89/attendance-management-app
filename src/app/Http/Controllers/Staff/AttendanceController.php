<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
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

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        $dates = collect();

        foreach (Carbon::parse($startOfMonth)->daysUntil($endOfMonth) as $date) {
            $attendance = Attendance::firstOrCreate([
                'user_id' => Auth::id(),
                'date' => $date->toDateString(),
            ]);

            $dates->push([
                'date' => $date,
                'formatted' => $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')',
                'attendance' => $attendance,
            ]);
        }

        return view('user.attendance.index', [
            'dates' => $dates,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        $correction = AttendanceCorrection::with('breakCorrections')
            ->where('attendance_id', $attendance->id)
            ->latest()
            ->first();

        $clockIn = $attendance->clock_in_time
            ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i')
            : '';

        $clockOut = $attendance->clock_out_time
            ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')
            : '';

        if ($correction && $correction->status === 'pending') {
            $breaks = $correction->breakCorrections;

            $formattedBreaks = $breaks->map(function ($break, $index) {
                return [
                    'label' => '休憩' . ($index + 1),
                    'start_value' => optional($break->requested_break_start)->format('H:i'),
                    'end_value'   => optional($break->requested_break_end)->format('H:i'),
                ];
            });
        } else {
            $breaks = $attendance->breaks;

            $formattedBreaks = $breaks->map(function ($break, $index) {
                return [
                    'label' => '休憩' . ($index + 1),
                    'start_name' => "break{$index}_start",
                    'end_name' => "break{$index}_end",
                    'start_value' => optional($break->break_start)->format('H:i'),
                    'end_value'   => optional($break->break_end)->format('H:i'),
                ];
            });
        }

        $newBreakField = [
            'label' => '休憩' . ($breaks->count() + 1),
            'start_name' => "break" . $breaks->count() . "_start",
            'end_name' => "break" . $breaks->count() . "_end",
        ];

        return view('user.attendance.show', compact(
            'attendance',
            'clockIn',
            'clockOut',
            'formattedBreaks',
            'newBreakField',
            'correction',
            'breaks'
        ));
    }
}
