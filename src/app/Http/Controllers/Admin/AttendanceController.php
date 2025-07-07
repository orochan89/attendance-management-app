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

        return view('admin.attendance.show', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $attendance->clock_in_time = $request->clock_in_time;
        $attendance->clock_out_time = $request->clock_out_time;
        $attendance->note = $request->note;
        $attendance->save();

        return redirect()->route('admin.attendance.show', $attendance->id)
            ->with('success', '勤怠情報を更新しました。');
    }
}
