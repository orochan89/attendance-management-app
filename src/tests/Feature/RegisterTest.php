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
        $response = $this->post($this->registerUrl, [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['name']);
        $response->assertSee('お名前を入力してください');
    }

    // testcase ID:1 メールアドレスが未入力の場合、バリデーションメッセージが表示される
    public function test_email_is_required_for_register()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $response->assertSee('メールアドレスを入力してください');
    }

    // testcase ID:1 パスワードが8文字未満の場合、バリデーションメッセージが表示される
    public function test_password_must_be_at_least_8_characters()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertSee('パスワードは8文字以上で入力してください');
    }

    // testcase ID:1 パスワード確認が一致しない場合、バリデーションメッセージが表示される
    public function test_password_confirmation_must_match()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertSee('パスワードと一致しません');
    }

    // testcase ID:1 パスワードが未入力の場合、バリデーションメッセージが表示される
    public function test_password_is_required_for_register()
    {
        $response = $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password']);
        $response->assertSee('パスワードを入力してください');
    }

    // testcase ID:1 フォームが正しく入力されていれば、正常にユーザーが登録される
    public function test_user_can_register_successfully()
    {
        $this->post($this->registerUrl, [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);
    }
}
