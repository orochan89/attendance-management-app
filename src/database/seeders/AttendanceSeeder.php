<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $staffUsers = User::where('role', 'staff')->get();

        foreach ($staffUsers as $user) {
            Attendance::factory(3)->create([
                'user_id' => $user->id,
            ])->each(function ($attendance) {
                BreakTime::factory()->create([
                    'attendance_id' => $attendance->id,
                ]);
            });
        }
    }
}
