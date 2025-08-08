<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;

class AttendanceApprovalController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $corrections = AttendanceCorrection::with(['user', 'attendance'])
            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.corrections.index', compact('corrections'));
    }

    public function show($attendance_correct_request)
    {
        $correction = AttendanceCorrection::with(['attendance.breaks', 'breakCorrections', 'user'])
            ->findOrFail($attendance_correct_request);

        return view('admin.corrections.show', compact('correction'));
    }

    public function approve(Request $request, $attendance_correct_request)
    {
        $correction = AttendanceCorrection::with(['attendance', 'breakCorrections'])
            ->findOrFail($attendance_correct_request);

        $attendance = $correction->attendance;

        $attendance->clock_in_time  = $correction->requested_clock_in;
        $attendance->clock_out_time = $correction->requested_clock_out;
        $attendance->save();

        $attendance->breaks()->delete();
        foreach ($correction->breakCorrections as $breakCorrection) {
            $attendance->breaks()->create([
                'break_start' => $breakCorrection->requested_break_start,
                'break_end'   => $breakCorrection->requested_break_end,
            ]);
        }

        $correction->status = 'approved';
        $correction->reviewed_by = auth()->id();
        $correction->reviewed_at = now();
        $correction->save();

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->back()->with('success', '修正申請を承認しました。');
    }
}
