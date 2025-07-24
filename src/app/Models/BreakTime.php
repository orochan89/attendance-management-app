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

    protected $casts = [
        'break_start' => 'datetime:H:i',
        'break_end'   => 'datetime:H:i',
    ];

    public function getDurationMinutesAttribute(): int
    {
        if ($this->break_start && $this->break_end) {
            return $this->break_start->diffInMinutes($this->break_end);
        }
        return 0;
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
