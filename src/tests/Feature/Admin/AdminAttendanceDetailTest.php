<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;
    protected $attendance;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-08-07');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->user = User::factory()->create();

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in_time' => Carbon::today()->setTime(9, 0),
            'clock_out_time' => Carbon::today()->setTime(18, 0),
            'status' => 'done',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);
    }

    // testcase ID:13 勤怠詳細画面に表示されるデータが選択したものになっている
    public function test_admin_can_view_attendance_detail()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.attendance.show', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // testcase ID:13 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_after_clock_out_shows_validation_error()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in_time' => '19:00',
            'clock_out_time' => '18:00',
            'break1_start' => '12:00',
            'break1_end' => '13:00',
            'reason' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'clock_in_time' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // testcase ID:13 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_start_after_clock_out_shows_validation_error()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'breaks' => [
                ['start' => '19:00', 'end' => '19:30'],
            ],
            'reason' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // testcase ID:13 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_end_after_clock_out_shows_validation_error()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'break1_start' => '17:00',
            'breaks' => [
                ['start' => '17:00', 'end' => '19:00'],
            ],
            'reason' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // testcase ID:13 備考欄が未入力の場合、エラーメッセージが表示される
    public function test_missing_reason_shows_validation_error()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.attendance.update', ['id' => $this->attendance->id]), [
            'clock_in_time' => '09:00',
            'clock_out_time' => '18:00',
            'break1_start' => '12:00',
            'break1_end' => '13:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }
}
