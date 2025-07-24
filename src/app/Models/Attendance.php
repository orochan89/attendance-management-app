<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in_time',
        'clock_out_time',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime:H:i',
        'clock_out_time' => 'datetime:H:i',
    ];

    public function getTotalBreakMinutesAttribute()
    {
        return $this->breaks->sum(function ($break) {
            return $break->duration_minutes;
        });
    }

    public function getTotalBreakFormattedAttribute()
    {
        $minutes = $this->total_break_minutes;
        return $minutes > 0
            ? sprintf('%02d:%02d', floor($minutes / 60), $minutes % 60)
            : '00:00';
    }

    public function getWorkTimeFormattedAttribute()
    {
        if ($this->clock_in_time && $this->clock_out_time) {
            $start = \Carbon\Carbon::parse($this->clock_in_time);
            $end = \Carbon\Carbon::parse($this->clock_out_time);
            $workMinutes = $start->diffInMinutes($end) - $this->total_break_minutes;

            return sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
        }
        return '00:00';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceCorrections()
    {
        return $this->hasMany(AttendanceCorrection::class);
    }
}
