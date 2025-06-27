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
}
