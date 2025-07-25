@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/admin-auth.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="admin">
        <h1 class="login__title">
            管理者ログイン
        </h1>
        <div class="login__container">
            <form class="login__form" action="" method="POST">
                @csrf
                <div class="login__form-group">
                    <label class="login__form-input-label" for="email">メールアドレス</label>
                    <input class="login__form-input" type="email" id="email" name="email"
                        value="{{ old('email') }}">
                </div>
                @error('email')
                    <div class="login__form-input-alert">{{ $message }}</div>
                @enderror
                <div class="login__form-group">
                    <label class="login__form-input-label" for="password">パスワード</label>
                    <input class="login__form-input" type="password" id="password" name="password">
                </div>
                @error('password')
                    <div class="login__form-input-alert">{{ $message }}</div>
                @enderror
                <div class="login__form-actions">
                    <button class="login__form-actions-button login__form-actions-button--submit"
                        type="submit">管理者ログインする</button>
                </div>
            </form>
        </div>
    </div>
@endsection
