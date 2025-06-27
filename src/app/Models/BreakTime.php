<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end'
    ];

    public function getDurationMinutesAttribute()
    {
        if ($this->break_start && $this->break_end) {
            $start = \Carbon\Carbon::parse($this->break_start);
            $end = \Carbon\Carbon::parse($this->break_end);
            return $start->diffInMinutes($end);
        }
        return 0;
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function breakCorrections()
    {
        return $this->hasMany(BreakCorrection::class);
    }
}
