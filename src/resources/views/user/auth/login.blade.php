@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="staff">
        <h1 class="login__title">
            ログイン
        </h1>
        <div class="login__container">
            <form class="login__form" action="{{ route('login') }}" method="POST" novalidate>
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
                        type="submit">ログインする</button>
                    <a class="login__form-actions-link" href="{{ route('register.form') }}">会員登録はこちら</a>
                </div>
            </form>
        </div>
    </div>
@endsection
