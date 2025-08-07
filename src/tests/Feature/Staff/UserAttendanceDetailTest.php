<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class UserAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2025-08-07');

        $this->user = User::factory()->create(['name' => 'テストユーザー']);
        $this->actingAs($this->user);

        $this->attendance = Attendance::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->copy()->toDateString(),
            'clock_in_time' => now()->copy()->setTime(11, 0),
            'clock_out_time' => now()->copy()->setTime(18, 0),
            'status' => 'done',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => now()->copy()->setTime(12, 0),
            'break_end' => now()->copy()->setTime(13, 0),
        ]);
        BreakTime::factory()->create([
            'attendance_id' => $this->attendance->id,
            'break_start' => now()->copy()->setTime(15, 0),
            'break_end' => now()->copy()->setTime(15, 15),
        ]);
    }

    // testcase ID:9 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
    public function test_attendance_detail_displays_user_name()
    {
        $response = $this->get(route('staff.attendance.show', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    // testcase ID:9 勤怠詳細画面の「日付」が選択した日付になっている
    public function test_attendance_detail_displays_attendance_date()
    {
        $response = $this->get(route('staff.attendance.show', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('2025年');
        $response->assertSee('8月7日');
    }

    // testcase ID:9 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
    public function test_attendance_detail_displays_clock_in_and_out_time()
    {
        $response = $this->get(route('staff.attendance.show', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('11:00');
        $response->assertSee('18:00');
    }

    // testcase ID:9 「休憩」にて記されている時間がログインユーザーの打刻と一致している
    public function test_attendance_detail_displays_total_break_time()
    {
        $response = $this->get(route('staff.attendance.show', $this->attendance->id));
        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('15:00');
        $response->assertSee('15:15');
    }
}
