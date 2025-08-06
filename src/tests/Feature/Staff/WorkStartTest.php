<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class WorkStartTest extends TestCase
{
    use RefreshDatabase;

    private string $url = '/attendance';

    // testcase ID:6 出勤ボタンが正しく機能する
    public function test_work_start_button_is_displayed_and_works_for_off_status()
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
        $response->assertSee('出勤');

        $response = $this->actingAs($user)->post('/attendance', [
            'action' => 'start_work',
        ]);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'working',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('出勤中');
    }

    // testcase ID:6 出勤は一日一回のみできる
    public function test_work_start_button_is_not_displayed_after_done()
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
        $response->assertDontSee('出勤');
    }

    // testcase ID:6 出勤時刻が管理画面で確認できる
    public function test_clock_in_time_is_visible_on_staff_attendance_list()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => null,
            'clock_out_time' => null,
            'status' => 'off',
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'start_work',
        ]);

        $updatedAttendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        $clockInFormatted = optional($updatedAttendance->clock_in_time)->format('H:i');

        $response = $this->actingAs($user)->get('/attendance/list?month=' . now()->format('Y-m'));
        $response->assertSee($clockInFormatted);
    }
}
