<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;

class AttendanceCorrectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'requested_clock_in' => $this->faker->time('H:i'),
            'requested_clock_out' => $this->faker->time('H:i'),
            'reason' => $this->faker->sentence,
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
        ];
    }
}
