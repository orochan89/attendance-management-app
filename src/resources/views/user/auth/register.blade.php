@extends('layouts.app')

@section('css')
@endsection

@section('content')
    @include('components.nav')

    <h1 class="register__title">
        会員登録
    </h1>
    <div class="register__container">
        <form class="register__form" action="" method="POST">
            @csrf
            <div class="register__form-group">
                <label class="register__form-input-label" for="name">名前</label>
                <input class="register__form-input" type="text" id="name" name="name" value="{{ old('name') }}">
            </div>
            @error('name')
                <div class="register__form-input-alert">{{ $message }}</div>
            @enderror
            <div class="register__form-group">
                <label class="register__form-input-label" for="email">メールアドレス</label>
                <input class="register__form-input" type="email" id="email" name="email"
                    value="{{ old('email') }}">
            </div>
            @error('email')
                <div class="register__form-input-alert">{{ $message }}</div>
            @enderror
            <div class="register__form-group">
                <label class="register__form-input-label" for="password">パスワード</label>
                <input class="register__form-input" type="password" id="password" name="password">
            </div>
            @error('password')
                <div class="register__form-input-alert">{{ $message }}</div>
            @enderror
            <div class="register__form-group">
                <label class="register__form-input-label" for="password_confirmation">パスワード確認</label>
                <input class="register__form-input" type="password" id="password_confirmation" name="password_confirmation">
            </div>
            @error('password_confirmation')
                <div class="register__form-input-alert">{{ $message }}</div>
            @enderror
            <div class="register__form-actions">
                <button class="register__form-actions-button register__form-actions-button--submit"
                    type="submit">登録する</button>
                <a class="register__form-actions-link" href="{{ route('login') }}">ログインはこちら</a>
            </div>
        </form>
    </div>
@endsection
