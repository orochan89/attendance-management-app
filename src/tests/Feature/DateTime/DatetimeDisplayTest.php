<?php

namespace Tests\Feature\DateTime;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class DatetimeDisplayTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    private string $url = '/attendance';

    // testcase ID:4 現在の日時情報がUIと同じ形式で出力されている
    public function test_attendance_status_view_contains_datetime_placeholders()
    {
        $user = User::factory()->create()->first();

        $response = $this->actingAs($user)->get($this->url);

        $response->assertStatus(200);

        $response->assertSee('class="status__datetime status__datetime--date current-date"', false);
        $response->assertSee('class="status__datetime status__datetime--time current-time"', false);
    }
}
