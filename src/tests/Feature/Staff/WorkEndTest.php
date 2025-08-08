<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class WorkEndTest extends TestCase
{

    use RefreshDatabase;

    private string $url = '/attendance';

    // testcase ID:8 退勤ボタンが正しく機能する
    public function test_work_end_button_is_displayed_and_works()
    {
        $user = User::factory()->create()->first();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => null,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance', ['action' => 'end_work']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'done',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    // testcase ID:8 退勤時刻が管理画面で確認できる
    public function test_clock_out_time_is_visible_on_staff_attendance_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => null,
            'clock_out_time' => null,
            'status' => 'off',
        ]);

        $this->actingAs($user)->post('/attendance', [
            'action' => 'start_work',
        ]);
        $this->actingAs($user)->post('/attendance', [
            'action' => 'end_work',
        ]);

        $updatedAttendance = Attendance::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        $clockOutFormatted = optional($updatedAttendance->clock_out_time)->format('H:i');

        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee($clockOutFormatted);
    }
}
