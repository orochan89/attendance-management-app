<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {

        $currentDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $attendances = Attendance::with(['user', 'breaks'])
            ->where('date', $currentDate->toDateString())
            ->get();

        return view('admin.attendance.index', [
            'attendances' => $attendances,
            'currentDate' => $currentDate,
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);

        $breaks = $attendance->breaks;

        return view('admin.attendance.show', compact('attendance', 'breaks'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('breaks')->findOrFail($id);

        // 出勤・退勤の更新
        $attendance->clock_in_time = $request->input('clock_in_time') ?: null;
        $attendance->clock_out_time = $request->input('clock_out_time') ?: null;
        $attendance->save();

        // 既存の休憩を更新
        $breaksInput = $request->input('breaks', []);
        foreach ($attendance->breaks as $index => $break) {
            if (isset($breaksInput[$index])) {
                $break->break_start = $breaksInput[$index]['start'] ?: null;
                $break->break_end = $breaksInput[$index]['end'] ?: null;
                $break->save();
            }
        }

        // 新しい休憩の追加（最後の1行用）
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
