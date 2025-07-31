@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="verify-email__container">
        <div class="verify-email-message">
            <p class="verify-email-message-text">
                登録していただいたメールアドレスに認証メールを送付しました。
            </p>
            <p class="verify-email-message-text">
                メール認証を完了してください。
            </p>
        </div>
        <form class="verify-email-form" action="{{ route('verification.send') }}" method="post">
            @csrf
            <div class="verify-email__button">
                <button class="verify-email__button--submit" type="submit">
                    認証はこちらから
                </button>
            </div>
        </form>
        <form class="resend-email-form" action="{{ route('verification.send') }}" method="post">
            @csrf
            <div class="resend-email__button">
                <button class="resend-email__button--submit" class="submit">
                    認証メールを再送する
                </button>
            </div>
        </form>
    </div>
@endsection
