<?php

namespace Tests\Feature\Staff;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrection;

class UserAttendanceCorrectionTest extends TestCase
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
    }

    // testcase ID:11 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_clock_in_later_than_clock_out_shows_validation_error()
    {
        $response = $this->post(route('staff.attendance.request_update', $this->attendance->id), [
            'requested_clock_in' => '19:00',
            'requested_clock_out' => '18:00',
            'reason' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'requested_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // testcase ID:11 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_start_later_than_clock_out_shows_validation_error()
    {
        $response = $this->post(route('staff.attendance.request_update', $this->attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'break1_start' => '19:00',
            'break1_end' => '19:30',
            'reason' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'break1_start' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // testcase ID:11 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
    public function test_break_end_later_than_clock_out_shows_validation_error()
    {
        $response = $this->post(route('staff.attendance.request_update', $this->attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'break1_start' => '17:00',
            'break1_end' => '19:00',
            'reason' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors([
            'break1_end' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    // testcase ID:11 備考欄が未入力の場合、エラーメッセージが表示される
    public function test_missing_reason_shows_validation_error()
    {
        $response = $this->post(route('staff.attendance.request_update', $this->attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'break1_start' => '12:00',
            'break1_end' => '13:00',
            'reason' => '', // ← 未入力
        ]);

        $response->assertSessionHasErrors([
            'reason' => '備考を記入してください',
        ]);
    }

    // testcase ID:11 修正申請処理が実行される
    public function test_correction_request_is_saved_and_pending()
    {
        $response = $this->post(route('staff.attendance.request_update', $this->attendance->id), [
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
            'break1_start' => '12:00',
            'break1_end' => '13:00',
            'reason' => '修正申請テスト',
        ]);

        $response->assertRedirect("/attendance/{$this->attendance->id}");

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_id' => $this->attendance->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'reason' => '修正申請テスト',
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        $response = $this->get('/admin/stamp_correction_request/list');
        $response->assertSee($this->user->name);
        $response->assertSee('修正申請テスト');

        $correction = AttendanceCorrection::where('attendance_id', $this->attendance->id)->first();

        $response = $this->get("/admin/stamp_correction_request/approve/{$correction->id}");
        $response->assertSee($this->user->name);
        $response->assertSee('修正申請テスト');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    // testcase ID:11 「承認待ち」にログインユーザーが行った申請が全て表示されている
    public function test_user_pending_requests_are_displayed()
    {
        $correction = AttendanceCorrection::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'status' => 'pending',
            'reason' => '申請理由テスト'
        ]);

        $response = $this->get('stamp_correction_request/list?status=pending');
        $response->assertStatus(200);
        $response->assertSee($correction->reason);
    }

    // testcase ID:11 「承認済み」に管理者が承認した修正申請が全て表示されている
    public function test_approved_requests_are_displayed()
    {
        $reasons = ['承認済み理由1', '承認済み理由2', '承認済み理由3'];

        foreach ($reasons as $reason) {
            AttendanceCorrection::factory()->create([
                'user_id' => $this->user->id,
                'attendance_id' => $this->attendance->id,
                'status' => 'approved',
                'reason' => $reason,
            ]);
        }

        $response = $this->get('/stamp_correction_request/list?status=approved');
        $response->assertStatus(200);

        foreach ($reasons as $reason) {
            $response->assertSee($reason);
        }
    }

    // testcase ID:11 各申請の「詳細」を押下すると申請詳細画面に遷移する
    public function test_request_detail_link_redirects_to_detail_page()
    {
        $correction = AttendanceCorrection::factory()->create([
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'reason' => '詳細画面表示テスト',
        ]);

        $response = $this->get('stamp_correction_request/list?status=pending');
        $response->assertSee(route('staff.attendance.show', $this->attendance->id));

        $detailResponse = $this->get(route('staff.attendance.show', $this->attendance->id));
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($correction->reason);
    }
}
