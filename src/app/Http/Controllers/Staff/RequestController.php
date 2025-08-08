<?php

namespace App\Http\Controllers\Staff;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Staff\AttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Http\Controllers\Controller;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');

        if (!in_array($status, ['pending', 'approved'])) {
            abort(404);
        }

        $corrections = AttendanceCorrection::with('attendance')
            ->where('user_id', Auth::id())
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.corrections.index', compact('corrections'));
    }

    public function requestUpdate(AttendanceCorrectionRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $pending = AttendanceCorrection::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();
        if ($pending) {
            return redirect()->back()->with('error', 'すでに承認待ちの申請があります。');
        }

        $correction = AttendanceCorrection::create([
            'user_id'            => Auth::id(),
            'attendance_id'      => $attendance->id,
            'requested_clock_in' => $request->input('requested_clock_in'),
            'requested_clock_out' => $request->input('requested_clock_out'),
            'reason'             => $request->input('reason'),
            'status'             => 'pending'
        ]);

        foreach ($this->getBreakPairs($request) as $break) {
            if ($break['start'] || $break['end']) {
                $correction->breakCorrections()->create([
                    'requested_break_start' => $break['start'],
                    'requested_break_end'   => $break['end'],
                ]);
            }
        }

        return redirect()->route('staff.attendance.show', ['id' => $id])
            ->with('success', '修正申請を送信しました。');
    }

    private function getBreakPairs($request): array
    {
        $breaks = [];
        $index = 1;
        while ($request->has("break{$index}_start") || $request->has("break{$index}_end")) {
            $breaks[] = [
                'start' => $request->input("break{$index}_start"),
                'end'   => $request->input("break{$index}_end"),
            ];
            $index++;
        }
        return $breaks;
    }
}
