<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-1 month', 'now');
        $clockIn = Carbon::instance($date)->setTime(9, 0);
        $clockOut = Carbon::instance($date)->setTime(18, 0);

        return [
            'user_id' => User::factory(),
            'date' => $clockIn->toDateString(),
            'clock_in_time' => $clockIn,
            'clock_out_time' => $clockOut,
            'status' => 'done',
        ];
    }
}
