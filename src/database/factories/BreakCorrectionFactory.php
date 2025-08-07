<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AttendanceCorrection;
use App\Models\BreakCorrection;

class BreakCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = $this->faker->time('H:i');
        $end = date('H:i', strtotime($start . ' +30 minutes'));

        return [
            'attendance_correction_id' => AttendanceCorrection::factory(),
            'requested_break_start' => $start,
            'requested_break_end' => $end,
        ];
    }
}
