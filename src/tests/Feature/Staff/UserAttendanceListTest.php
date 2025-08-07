<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class UserAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2025-08-07'));
    }


    // testcase ID:9 自分が行った勤怠情報が全て表示されている
    public function test_attendance_list_shows_all_user_attendance()
    {
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->copy()->toDateString(),
            'clock_in_time' => now()->copy()->setTime(11, 0),
            'clock_out_time' => now()->copy()->setTime(18, 0),
            'status' => 'done',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->copy()->subDays()->toDateString(),
            'clock_in_time' => now()->copy()->subDays()->setTime(11, 0),
            'clock_out_time' => now()->copy()->subDays()->setTime(18, 0),
            'status' => 'done',
        ]);
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->copy()->subDays(2)->toDateString(),
            'clock_in_time' => now()->copy()->subDays(2)->setTime(11, 0),
            'clock_out_time' => now()->copy()->subDays(2)->setTime(18, 0),
            'status' => 'done',
        ]);

        $response = $this->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2025/08');
        $this->assertEquals(3, substr_count($response->getContent(), '11:00'));
        $this->assertEquals(3, substr_count($response->getContent(), '18:00'));
    }

    // testcase ID:9 勤怠一覧画面に遷移した際に現在の月が表示される
    public function test_attendance_list_shows_this_month()
    {
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->copy()->toDateString(),
            'clock_in_time' => now()->copy()->setTime(11, 0),
            'clock_out_time' => now()->copy()->setTime(18, 0),
            'status' => 'done',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2025/08');
        $response->assertSee('11:00');
        $response->assertSee('18:00');
    }

    // testcase ID:9 「前月」を押下した時に表示月の前月の情報が表示される
    public function test_attendance_list_shows_previous_month()
    {
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->copy()->subMonth()->toDateString(),
            'clock_in_time' => now()->copy()->subMonth()->setTime(11, 0),
            'clock_out_time' => now()->copy()->subMonth()->setTime(18, 0),
            'status' => 'done',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2025/08');
        $response->assertSee('前月');

        $prevMonthUrl = '/attendance/list?month=2025-07';

        $response = $this->get($prevMonthUrl);
        $response->assertStatus(200);
        $response->assertSee('2025/07');
        $response->assertSee('11:00');
        $response->assertSee('18:00');
    }

    // testcase ID:9 「翌月」を押下した時に表示月の前月の情報が表示される
    public function test_attendance_list_shows_next_month()
    {
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->copy()->addMonth()->toDateString(),
            'clock_in_time' => now()->copy()->addMonth()->setTime(11, 0),
            'clock_out_time' => now()->copy()->addMonth()->setTime(18, 0),
            'status' => 'done',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('2025/08');
        $response->assertSee('翌月');

        $nextMonthUrl = '/attendance/list?month=2025-09';

        $response = $this->get($nextMonthUrl);
        $response->assertStatus(200);
        $response->assertSee('2025/09');
        $response->assertSee('11:00');
        $response->assertSee('18:00');
    }

    public function test_clicking_detail_link_redirects_to_attendance_detail()
    {
        $user = User::factory()->create()->first();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->copy()->toDateString(),
            'clock_in_time' => now()->copy()->setTime(11, 0),
            'clock_out_time' => now()->copy()->setTime(18, 0),
            'status' => 'done',
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('詳細');
        $response->assertSee(route('staff.attendance.show', $attendance->id));

        $detailResponse = $this->get(route('staff.attendance.show', $attendance->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('2025年');
        $detailResponse->assertSee('8月7日');
    }
}
