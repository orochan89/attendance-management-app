@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/correction-detail.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="admin">
        <h1 class="attendance-detail__title">勤怠詳細</h1>
        <form class="attendance-detail__form"
            action="{{ route('admin.request.approve.submit', ['attendance_correct_request' => $correction->id]) }}"
            method="POST">
            @csrf
            <table class="attendance-detail__table">
                <tbody class="attendance-detail__tbody">

                    {{-- 名前 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">名前</th>
                        <td class="attendance-detail__cell" colspan="2">
                            {{ $correction->user->name ?? '不明なユーザー' }}
                        </td>
                    </tr>

                    {{-- 日付 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">日付</th>
                        <td class="attendance-detail__cell">
                            {{ \Carbon\Carbon::parse($correction->attendance->date)->format('Y年') }}
                        </td>
                        <td class="attendance-detail__cell">
                            {{ \Carbon\Carbon::parse($correction->attendance->date)->format('n月j日') }}
                        </td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">出勤・退勤</th>
                        <td class="attendance-detail__cell">
                            <p>
                                {{ $correction->requested_clock_in
                                    ? \Carbon\Carbon::parse($correction->requested_clock_in)->format('H:i')
                                    : '-' }}
                            </p>
                        </td>
                        <td class="attendance-detail__cell">
                            <p>
                                {{ $correction->requested_clock_out
                                    ? \Carbon\Carbon::parse($correction->requested_clock_out)->format('H:i')
                                    : '-' }}
                            </p>
                        </td>
                    </tr>

                    {{-- 休憩欄（correction内の休憩修正を動的に表示） --}}
                    @if ($correction->breakCorrections->count() > 0)
                        @foreach ($correction->breakCorrections as $index => $break)
                            <tr class="attendance-detail__row">
                                <th class="attendance-detail__cell attendance-detail__cell--label">
                                    休憩{{ $index + 1 }}
                                </th>
                                <td class="attendance-detail__cell">
                                    <p>
                                        {{ $break->requested_break_start ? \Carbon\Carbon::parse($break->requested_break_start)->format('H:i') : '-' }}
                                    </p>
                                </td>
                                <td class="attendance-detail__cell">
                                    <p>
                                        {{ $break->requested_break_end ? \Carbon\Carbon::parse($break->requested_break_end)->format('H:i') : '-' }}
                                    </p>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="attendance-detail__row">
                            <th class="attendance-detail__cell attendance-detail__cell--label">休憩</th>
                            <td class="attendance-detail__cell" colspan="2">申請された休憩はありません。</td>
                        </tr>
                    @endif

                    {{-- 備考 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">申請理由</th>
                        <td class="attendance-detail__cell" colspan="2">
                            <p>{{ $correction->reason ?? '未記入' }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- 承認ボタン --}}
            <div class="attendance-detail__actions">
                @if ($correction->status === 'pending')
                    <button class="attendance-detail__button attendance-detail__button--submit" type="submit"
                        name="action" value="approve">
                        承認する
                    </button>
                @else
                    <button class="attendance-detail__button attendance-detail__button--submit" type="submit"
                        name="action" value="approve" disabled>
                        承認済み
                    </button>
                @endif
            </div>
        </form>
    </div>
@endsection
