<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserLoginTest extends TestCase
{
    use RefreshDatabase;

    protected string $loginUrl = '/login';

    // testcase ID:2 メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required_for_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff'
        ]);

        $response = $this
            ->from($this->loginUrl)
            ->post($this->loginUrl, [
                'email' => '',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors(['email']);
        $this->followingRedirects()
            ->get($this->loginUrl)
            ->assertSee('メールアドレスを入力してください');
    }

    // testcase ID:2 パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required_for_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff'
        ]);

        $response = $this
            ->from($this->loginUrl)
            ->post($this->loginUrl, [
                'email' => $user->email,
                'password' => '',
            ]);

        $response->assertSessionHasErrors(['password']);
        $this->followingRedirects()
            ->get($this->loginUrl)
            ->assertSee('パスワードを入力してください');
    }

    // testcase ID:2 登録内容と一致しない場合、バリデーションメッセージが表示される
    public function test_invalid_credentials_show_error_message()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'staff'
        ]);

        $response = $this
            ->from($this->loginUrl)
            ->post($this->loginUrl, [
                'email' => 'wrong@example.com',
                'password' => 'different',
            ]);

        $response->assertSessionHasErrors(['email']);
        $this->followingRedirects()
            ->get($this->loginUrl)
            ->assertSee('ログイン情報が登録されていません');
    }
}
