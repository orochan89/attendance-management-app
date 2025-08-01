<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Attendance;


class StaffAttendanceController extends Controller
{
    public function index()
    {
        // role = 'staff' のユーザーを一覧取得
        $users = User::where('role', 'staff')->get();

        return view('admin.staff.index', compact('users'));
    }

    public function show($id, Request $request)
    {
        $user = User::findOrFail($id);

        $currentMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now()->startOfMonth();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $dates = collect();

        foreach ($startOfMonth->daysUntil($endOfMonth->copy()->addDay()) as $date) {
            $attendance = Attendance::firstOrCreate([
                'user_id' => $user->id,
                'date' => $date->toDateString(),
            ]);

            $dates->push([
                'date' => $date,
                'formatted' => $date->format('m/d') . '(' . $weekdays[$date->dayOfWeek] . ')',
                'attendance' => $attendance,
            ]);
        }

        return view('admin.staff.attendances', [
            'user' => $user,
            'dates' => $dates,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    public function exportCsv($id, Request $request): StreamedResponse
    {
        $month = $request->input('month');
        $targetMonth = $month ? Carbon::parse($month) : now();
        $start = $targetMonth->copy()->startOfMonth()->toDateString();
        $end = $targetMonth->copy()->endOfMonth()->toDateString();

        $user = User::findOrFail($id);
        $attendances = $user->attendances()
            ->with('breaks')
            ->whereBetween('date', [$start, $end])
            ->get();

        $fileName = "{$user->name}_{$targetMonth->format('Y_m')}_attendances.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        return response()->stream(function () use ($attendances) {
            $stream = fopen('php://output', 'w');

            fwrite($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー行
            fputcsv($stream, ['日付', '出勤', '退勤', '休憩（分）', '労働時間（分）']);

            foreach ($attendances as $attendance) {
                $date = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
                $in = optional($attendance->clock_in_time)->format('H:i') ?? '';
                $out = optional($attendance->clock_out_time)->format('H:i') ?? '';

                $breakMinutes = $attendance->breaks->reduce(function ($carry, $break) {
                    if ($break->break_start && $break->break_end) {
                        return $carry + \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
                    }
                    return $carry;
                }, 0);

                $workMinutes = ($attendance->clock_in_time && $attendance->clock_out_time)
                    ? \Carbon\Carbon::parse($attendance->clock_in_time)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out_time)) - $breakMinutes
                    : '';

                fputcsv($stream, [$date, $in, $out, $breakMinutes, $workMinutes]);
            }

            fclose($stream);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
