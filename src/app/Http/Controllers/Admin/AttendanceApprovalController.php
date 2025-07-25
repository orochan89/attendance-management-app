<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AttendanceCorrection;

class AttendanceApprovalController extends Controller
{
    public function index(Request $request)
    {
        // ステータスをフィルタリング（デフォルトは pending）
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

        // 勤怠の修正
        $attendance->clock_in_time  = $correction->requested_clock_in;
        $attendance->clock_out_time = $correction->requested_clock_out;
        $attendance->save();

        // 休憩時間を更新
        $attendance->breaks()->delete();
        foreach ($correction->breakCorrections as $breakCorrection) {
            $attendance->breaks()->create([
                'break_start' => $breakCorrection->requested_break_start,
                'break_end'   => $breakCorrection->requested_break_end,
            ]);
        }

        // ステータス更新
        $correction->status = 'approved';
        $correction->save();

        return redirect()
            ->route('admin.request.list')
            ->with('success', '修正申請を承認しました。');
    }
}
