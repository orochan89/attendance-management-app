<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;

class RequestController extends Controller
{
    public function index()
    {
        $corrections = AttendanceCorrection::with('attendance')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function requestUpdate(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        AttendanceCorrection::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'requested_clock_in' => $request->input('requested_clock_in'),
            'requested_clock_out' => $request->input('requested_clock_out'),
            'reason' => $request->input('reason'),
            'status' => 'pending'
        ]);

        return redirect()->route('attendance.show', $attendance->id)
            ->with('status', '修正申請を送信しました。');
    }
}
