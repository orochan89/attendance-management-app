<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\BreakCorrection;

class AdminCorrectionApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;
    protected $attendances;
    protected $corrections;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2025-08-07');

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staffUsers = User::factory()->count(3)->create(['role' => 'staff']);

        $this->attendances = [];
        $this->corrections = [];

        foreach ($this->staffUsers as $index => $user) {
            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'date' => Carbon::today()->toDateString(),
                'clock_in_time' => '09:00',
                'clock_out_time' => '18:00',
            ]);

            $this->attendances[] = $attendance;

            $pending = AttendanceCorrection::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'requested_clock_in' => '10:00',
                'requested_clock_out' => '19:00',
                'reason' => "寝坊（{$index}）",
                'status' => 'pending',
            ]);
            $this->corrections[] = $pending;

            $approved = AttendanceCorrection::factory()->create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'requested_clock_in' => '11:00',
                'requested_clock_out' => '20:00',
                'reason' => "遅延（{$index}）",
                'status' => 'approved',
            ]);
            $this->corrections[] = $approved;

            BreakCorrection::factory()->create([
                'attendance_correction_id' => $approved->id,
                'requested_break_start' => '14:00',
                'requested_break_end' => '14:30',
            ]);
        }
    }
    // testcase ID:15 承認待ちの修正申請が全て表示されている
    public function test_pending_corrections_are_displayed()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.request.list', ['status' => 'pending']));

        $response->assertStatus(200);
        foreach ($this->corrections as $correction) {
            if ($correction->status === 'pending') {
                $response->assertSee($correction->reason);
                $response->assertSee('承認待ち');
            } else {
                $response->assertDontSee($correction->reason);
            }
        }
    }

    // testcase ID:15 承認済みの修正申請が全て表示されている
    public function test_approved_corrections_are_displayed()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.request.list', ['status' => 'approved']));

        $response->assertStatus(200);
        foreach ($this->corrections as $correction) {
            if ($correction->status === 'approved') {
                $response->assertSee($correction->reason);
                $response->assertSee('承認済み');
            } else {
                $response->assertDontSee($correction->reason);
            }
        }
    }

    // testcase ID:15 修正申請の詳細内容が正しく表示されている
    public function test_correction_detail_displays_correct_information()
    {
        $target = $this->corrections[0];

        $response = $this->actingAs($this->admin)
            ->get(route('admin.request.approve.show', ['attendance_correct_request' => $target->id]));

        $response->assertStatus(200)
            ->assertSee($target->requested_clock_in->format('H:i'))
            ->assertSee($target->requested_clock_out->format('H:i'))
            ->assertSee($target->reason);
    }

    // testcase ID:15 修正申請の承認処理が正しく行われる
    public function test_admin_can_approve_correction()
    {
        $target = $this->corrections[0];
        $attendance = $target->attendance;

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.request.approve.submit', ['attendance_correct_request' => $target->id]), [
                'action' => 'approve',
            ]);

        $response->assertStatus(200)
            ->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $target->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_time' => $target->requested_clock_in->format('H:i:s'),
            'clock_out_time' => $target->requested_clock_out->format('H:i:s'),
        ]);
    }
}
