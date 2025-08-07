<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffManagementTest extends TestCase
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

    // testcase ID:14 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
    public function test_admin_can_view_all_staff_names_and_emails()
    {
        $response = $this->actingAs($this->admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        foreach ($this->users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    // testcase ID:14 ユーザーの勤怠情報が正しく表示される
    public function test_admin_can_view_staff_attendance_of_current_month()
    {
        $user = $this->users->first();

        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$user->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('2025/08');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }

    // testcase ID:14 「前月」を押下した際に表示月の前月の情報が表示される
    public function test_admin_can_view_previous_month_attendance()
    {
        $user = $this->users->first();
        $prevMonth = $this->date->copy()->subMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $this->date->copy()->subMonth()->setDay(1)->toDateString(),
            'clock_in_time' => '10:00',
            'clock_out_time' => '18:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$user->id}?month={$prevMonth}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('2025/07');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
    }

    // testcase ID:14 「翌月」を押下した際に表示月の翌月の情報が表示される
    public function test_admin_can_view_next_month_attendance()
    {
        $user = $this->users->first();
        $nextMonth = $this->date->copy()->addMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $this->date->copy()->addMonth()->setDay(1)->toDateString(),
            'clock_in_time' => '11:00',
            'clock_out_time' => '19:00',
            'status' => 'done',
        ]);

        $response = $this->actingAs($this->admin)->get("/admin/attendance/staff/{$user->id}?month={$nextMonth}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('2025/09');
        $response->assertSee('11:00');
        $response->assertSee('19:00');
    }

    // testcase ID:14 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
    public function test_admin_can_access_attendance_detail_page()
    {
        $user = $this->users->first();
        $attendance = Attendance::where('user_id', $user->id)->first();

        $response = $this->actingAs($this->admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($this->date->format('Y年'));
        $response->assertSee($this->date->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }
}
