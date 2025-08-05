<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'email_verified_at' => now(),
        ]);

        $staffUsers = collect();
        foreach (range(1, 10) as $i) {
            $user = User::factory()->create([
                'email' => 'user' . str_pad($i, 2, '0', STR_PAD_LEFT) . '@example.com',
            ]);

            $staffUsers->push($user);
        }

        $current = Carbon::now()->startOfMonth();
        $start = $current->copy()->subMonth()->startOfMonth();
        $end = $start->copy()->endOfMonth();

        foreach ($staffUsers as $user) {
            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if ($date->isWeekend()) {
                    continue;
                }

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'clock_in_time' => $date->copy()->setTime(9, 0),
                    'clock_out_time' => $date->copy()->setTime(18, 0),
                    'status' => 'done',
                ]);

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start' => $date->copy()->setTime(12, 0),
                    'break_end' => $date->copy()->setTime(13, 0),
                ]);
            }
        }
    }
}
