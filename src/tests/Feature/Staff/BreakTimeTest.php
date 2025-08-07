<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class BreakTimeTest extends TestCase
{
    use RefreshDatabase;

    private string $url = '/attendance';

    // testcase ID:7 休憩ボタンが正しく機能する
    public function test_break_start_button_is_displayed_and_works()
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
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance', ['action' => 'start_break']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'break',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩中');
    }

    // testcase ID:7 休憩は一日に何回でもできる
    public function test_user_can_take_multiple_breaks()
    {
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => null,
            'status' => 'working',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'start_break']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'break',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance', ['action' => 'resume_work']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'working',
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('休憩入');
    }

    // testcase ID:7 休憩戻ボタンが正しく機能する
    public function test_resume_work_button_works()
    {
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => null,
            'status' => 'working',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'start_break']);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance', ['action' => 'resume_work']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('出勤中');
    }

    // testcase ID:7 休憩戻は一日に何回でもできる
    public function test_user_can_return_from_multiple_breaks()
    {
        $user = User::factory()->create()->first();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => null,
            'status' => 'working',
        ]);

        $this->actingAs($user)->post('/attendance', ['action' => 'start_break']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'break',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance', ['action' => 'resume_work']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'working',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance', ['action' => 'start_break']);

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'break',
        ]);

        $response = $this->actingAs($user)->get($this->url);
        $response->assertSee('休憩戻');
    }

    // testcase ID:7 休憩時刻が勤怠一覧画面で確認できる
    public function test_break_duration_is_displayed_on_attendance_list()
    {
        $user = User::factory()->create()->first();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in_time' => now()->setTime(9, 0),
            'clock_out_time' => now()->setTime(18, 0),
            'status' => 'done',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->setTime(12, 0),
            'break_end' => now()->setTime(12, 30),
        ]);

        $expectedBreakTime = '00:30';

        $response = $this->actingAs($user)->get('/attendance/list?month=' . now()->format('Y-m'));

        $response->assertSee($expectedBreakTime);
    }
}
