<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    protected string $adminLoginUrl = '/admin/login';

    // testcase ID:3 メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required_for_admin_login()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this
            ->from($this->adminLoginUrl)
            ->post($this->adminLoginUrl, [
                'email' => '',
                'password' => 'password',
            ]);

        $response->assertSessionHasErrors(['email']);
        $this->followingRedirects()
            ->get($this->adminLoginUrl)
            ->assertSee('メールアドレスを入力してください');
    }

    // testcase ID:3 パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required_for_admin_login()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this
            ->from($this->adminLoginUrl)
            ->post($this->adminLoginUrl, [
                'email' => $admin->email,
                'password' => '',
            ]);

        $response->assertSessionHasErrors(['password']);
        $this->followingRedirects()
            ->get($this->adminLoginUrl)
            ->assertSee('パスワードを入力してください');
    }

    // testcase ID:3 ログイン情報が一致しない場合、バリデーションメッセージが表示される
    public function test_invalid_admin_credentials_show_error_message()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $response = $this
            ->from($this->adminLoginUrl)
            ->post($this->adminLoginUrl, [
                'email' => 'wrong@example.com',
                'password' => 'wrongpassword',
            ]);

        $response->assertSessionHasErrors(['email']);
        $this->followingRedirects()
            ->get($this->adminLoginUrl)
            ->assertSee('ログイン情報が登録されていません');
    }
}
