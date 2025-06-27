<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

use App\Models\BreakTime;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition(): array
    {
        $start = Carbon::now()->setTime(12, 0);
        $end = (clone $start)->addMinutes(45);

        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $start,
            'break_end' => $end,
        ];
    }
}
