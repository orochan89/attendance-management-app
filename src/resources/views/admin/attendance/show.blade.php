@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance-detail.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="admin">
        <h1 class="attendance-detail__title">勤怠詳細（管理者用）</h1>
        <form class="attendance-detail__form" action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}"
            method="POST">
            @csrf
            <table class="attendance-detail__table">
                <tbody class="attendance-detail__tbody">

                    {{-- 名前 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">名前</th>
                        <td class="attendance-detail__cell" colspan="2">
                            {{ $attendance->user->name ?? '不明なユーザー' }}
                        </td>
                    </tr>

                    {{-- 日付 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">日付</th>
                        <td class="attendance-detail__cell">{{ $attendance->date->format('Y年') }}</td>
                        <td class="attendance-detail__cell">{{ $attendance->date->format('n月j日') }}</td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">出勤・退勤</th>
                        <td class="attendance-detail__cell">
                            <input class="attendance-detail__time-input" type="time" name="clock_in_time"
                                value="{{ old('clock_in_time', $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '') }}">
                        </td>
                        <td class="attendance-detail__cell">
                            <input class="attendance-detail__time-input" type="time" name="clock_out_time"
                                value="{{ old('clock_out_time', $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '') }}">
                        </td>
                    </tr>

                    {{-- 登録済み休憩欄 --}}
                    @foreach ($breaks as $index => $break)
                        <tr class="attendance-detail__row">
                            <th class="attendance-detail__cell attendance-detail__cell--label">
                                休憩{{ $index + 1 }}
                            </th>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time"
                                    name="breaks[{{ $index }}][start]"
                                    value="{{ old('breaks.' . $index . '.start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                            </td>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time"
                                    name="breaks[{{ $index }}][end]"
                                    value="{{ old('breaks.' . $index . '.end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                            </td>
                        </tr>
                    @endforeach

                    {{-- 新規追加用の休憩欄（1行） --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">
                            休憩{{ count($breaks) + 1 }}
                        </th>
                        <td class="attendance-detail__cell">
                            <input class="attendance-detail__time-input" type="time"
                                name="breaks[{{ count($breaks) }}][start]" value="">
                        </td>
                        <td class="attendance-detail__cell">
                            <input class="attendance-detail__time-input" type="time"
                                name="breaks[{{ count($breaks) }}][end]" value="">
                        </td>
                    </tr>

                    {{-- 備考 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">備考</th>
                        <td class="attendance-detail__cell" colspan="2">
                            <textarea class="attendance-detail__textarea" name="reason" rows="3">{{ old('reason', $attendance->reason) }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- ボタン --}}
            <div class="attendance-detail__actions">
                <button class="attendance-detail__button attendance-detail__button--submit" type="submit" name="action"
                    value="update">
                    修正
                </button>
            </div>
        </form>
    </div>
@endsection
