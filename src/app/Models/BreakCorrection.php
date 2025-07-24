<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_id',
        'requested_break_start',
        'requested_break_end'
    ];

    protected $casts = [
        'requested_break_start' => 'datetime:H:i',
        'requested_break_end'   => 'datetime:H:i',
    ];

    public function attendanceCorrection()
    {
        return $this->belongsTo(AttendanceCorrection::class);
    }
}
