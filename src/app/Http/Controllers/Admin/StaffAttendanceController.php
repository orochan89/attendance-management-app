<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
