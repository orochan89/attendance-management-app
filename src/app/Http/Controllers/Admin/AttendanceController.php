<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminUpdateAttendanceRequest;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : now();

        $attendances = Attendance::with(['user', 'breaks'])
            ->where('date', $currentDate->format('Y-m-d'))
            ->whereNotNull('clock_in_time')
            ->get();

        $processedAttendances = $attendances->map(function ($attendance) {
            $hasClockInOut = $attendance->clock_in_time && $attendance->clock_out_time;

            if ($hasClockInOut) {
                $clockIn = Carbon::parse($attendance->clock_in_time)->format('H:i');
                $clockOut = Carbon::parse($attendance->clock_out_time)->format('H:i');

                $totalBreakMinutes = $attendance->breaks->reduce(function ($carry, $break) {
                    if ($break->break_start && $break->break_end) {
                        $start = Carbon::parse($break->break_start);
                        $end = Carbon::parse($break->break_end);
                        return $carry + $start->diffInMinutes($end);
                    }
                    return $carry;
                }, 0);

                $breakFormatted = sprintf('%d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                $start = Carbon::parse($attendance->clock_in_time);
                $end = Carbon::parse($attendance->clock_out_time);
                $workMinutes = $start->diffInMinutes($end) - $totalBreakMinutes;
                $workFormatted = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
            } else {
                $clockIn = '';
                $clockOut = '';
                $breakFormatted = '';
                $workFormatted = '';
            }

            return [
                'id' => $attendance->id,
                'user_name' => $attendance->user->name ?? '不明なユーザー',
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break_time' => $breakFormatted,
                'work_time' => $workFormatted,
            ];
        });

        return view('admin.attendance.index', [
            'attendances' => $processedAttendances,
            'currentDate' => $currentDate,
            'prevDate' => $currentDate->copy()->subDay()->format('Y-m-d'),
            'nextDate' => $currentDate->copy()->addDay()->format('Y-m-d'),
        ]);
    }

    public function show($id)
    {
        if (str_starts_with($id, 'date-')) {
            preg_match('/date-(\d{8})-user-(\d+)/', $id, $matches);
            if (!$matches) {
                abort(404);
            }

            $date = Carbon::createFromFormat('Ymd', $matches[1])->toDateString();
            $userId = (int)$matches[2];

            $attendance = Attendance::firstOrCreate([
                'user_id' => $userId,
                'date' => $date,
            ]);
        } else {
            $attendance = Attendance::findOrFail($id);
        }

        $attendance->load('user', 'breaks');

        $breaks = $attendance->breaks;

        return view('admin.attendance.show', [
            'attendance' => $attendance,
            'breaks' => $breaks,
        ]);
    }

    public function update(AdminUpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        $attendance->clock_in_time = $request->input('clock_in_time') ?: null;
        $attendance->clock_out_time = $request->input('clock_out_time') ?: null;
        $attendance->reason = $request->input('reason') ?: null;
        $attendance->save();

        $breaksInput = $request->input('breaks', []);
        foreach ($attendance->breaks as $index => $break) {
            if (isset($breaksInput[$index])) {
                $break->break_start = $breaksInput[$index]['start'] ?: null;
                $break->break_end = $breaksInput[$index]['end'] ?: null;
                $break->save();
            }
        }

        $newBreakIndex = count($attendance->breaks);
        if (!empty($breaksInput[$newBreakIndex]['start']) || !empty($breaksInput[$newBreakIndex]['end'])) {
            $attendance->breaks()->create([
                'break_start' => $breaksInput[$newBreakIndex]['start'] ?: null,
                'break_end' => $breaksInput[$newBreakIndex]['end'] ?: null,
            ]);
        }

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('success', '勤怠情報を更新しました。');
    }
}
