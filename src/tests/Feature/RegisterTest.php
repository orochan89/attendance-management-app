<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected string $registerUrl = '/register';

    // testcase ID:1 名前が入力されていない場合、バリデーションメッセージが表示される
    public function test_name_is_required_for_register()
    {
        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', [
                'name' => '',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSee('お名前を入力してください');
    }

    // testcase ID:1 メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required_for_register()
    {
        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => '',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $response->assertSee('メールアドレスを入力してください');
    }

    // testcase ID:1 パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'pass123',
                'password_confirmation' => 'pass123',
            ]);

        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    // testcase ID:1 パスワード確認が一致しない場合、バリデーションメッセージが表示される
    public function test_password_confirmation_must_match()
    {
        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', [
                'name' => 'テストユーザー',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'different',
            ]);

        $response->assertSee('パスワードと一致しません');
    }

    // testcase ID:1 パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required_for_register()
    {
        $response = $this
            ->from('/register')
            ->followingRedirects()
            ->post('/register', [
                'name' => '',
                'email' => 'test@example.com',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertSee('パスワードを入力してください');
    }

    // testcase ID:1 フォームが正しく入力されていれば、正常にユーザーが登録される
    public function test_user_can_register_successfully()
    {
        $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);
    }
}
