<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    private string $url = '/attendance';

    // testcase ID:5 勤務外の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_off_is_displayed()
    {
        $user = User::factory()->create()->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => null,
            'clock_out_time' => null,
            'status' => 'off',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('勤務外');
    }

    // testcase ID:5 勤務中の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_working_is_displayed()
    {
        $user = User::factory()->create()->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => null,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('出勤中');
    }

    // testcase ID:5 休憩中の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_break_is_displayed()
    {
        $user = User::factory()->create()->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => null,
            'status' => 'break',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('休憩中');
    }

    // testcase ID:5 退勤済の場合、勤怠ステータスが正しく表示される
    public function test_attendance_status_done_is_displayed()
    {
        $user = User::factory()->create()->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('退勤済');
    }
}
