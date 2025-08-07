<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $users;
    protected $date;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-08-07');
        $this->date = Carbon::today();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->users = User::factory()->count(3)->create();

        foreach ($this->users as $index => $user) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => $this->date->toDateString(),
                'clock_in_time' => $this->date->copy()->setTime(9 + $index, 0),
                'clock_out_time' => $this->date->copy()->setTime(17 + $index, 0),
                'status' => 'done',
            ]);
        }
    }

    // testcase ID:12 その日になされた全ユーザーの勤怠情報が正確に確認できる
    public function test_attendance_list_shows_today_attendance()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/list");

        $response->assertStatus(200);

        foreach ($this->users as $index => $user) {
            $response->assertSee($user->name);
            $response->assertSee((string)(9 + $index) . ':00');
            $response->assertSee((string)(17 + $index) . ':00');
        }
    }

    // testcase ID:12 遷移した際に現在の日付が表示される
    public function test_attendance_list_shows_today_date()
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/list?date={$this->date->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee($this->date->format('Y年n月j日'));
    }

    // testcase ID:12 「前日」を押下した際に前の日の勤怠情報が表示される
    public function test_attendance_list_shows_previous_day_attendance()
    {
        $prevDate = $this->date->copy()->subDay();

        Attendance::factory()->create([
            'user_id' => $this->users->first()->id,
            'date' => $prevDate->toDateString(),
            'clock_in_time' => $prevDate->copy()->setTime(10, 0),
            'clock_out_time' => $prevDate->copy()->setTime(17, 0),
            'status' => 'done',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/list?date={$prevDate->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee('10:00');
        $response->assertSee('17:00');
        $response->assertSee($prevDate->format('Y/m/d'));
    }

    // testcase ID:12 「翌日」を押下した際に次の日の勤怠情報が表示される
    public function test_attendance_list_shows_next_day_attendance()
    {
        $nextDate = $this->date->copy()->addDay();

        Attendance::factory()->create([
            'user_id' => $this->users->first()->id,
            'date' => $nextDate->toDateString(),
            'clock_in_time' => $nextDate->copy()->setTime(8, 30),
            'clock_out_time' => $nextDate->copy()->setTime(17, 30),
            'status' => 'done',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/attendance/list?date={$nextDate->format('Y-m-d')}");

        $response->assertStatus(200);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }
}
