@extends('layouts.app')

@section('css')
@endsection

@section('content')
    @include('components.nav')

    <div class="admin">
        <h1 class="attendance-detail__title">勤怠詳細</h1>
        <form class="attendance-detail__form" action="" method="POST">
            @csrf
            <table class="attendance-detail__table">
                <tbody class="attendance-detail__tbody">
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">
                            名前
                        </th>
                        <td class="attendance-detail__cell" colspan="2">
                            {{ $attendance->user->name ?? '不明なユーザー' }}
                        </td>
                    </tr>
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">
                            日付
                        </th>
                        <td class="attendance-detail__cell">
                            {{ $attendance->date->format('Y年') }}
                        </td>
                        <td class="attendance-detail__cell">
                            {{ $attendance->date->format('n月j日') }}
                        </td>
                    </tr>
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">
                            出勤・退勤
                        </th>
                        <td class="attendance-detail__cell">
                            <input type="time" name="clock_in_time"
                                value="{{ old('clock_in_time', $attendance->clock_in_time->format('H:i')) }}">
                        </td>
                        <td>
                            <input type="time" name="clock_out_time"
                                value="{{ old('clock_out_time', $attendance->clock_out_time->format('H:i')) }}">
                        </td>
                    </tr>
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">
                            休憩
                        </th>
                        <td class="attendance-detail__cell">
                            <input type="time" name="break1_start"
                                value="{{ old('break1_start', $breaks[0]->break_start?->format('H:i') ?? '') }}">
                        </td>
                        <td class="attendance-detail__cell">
                            <input type="time" name="break1_end"
                                value="{{ old('break1_end', $breaks[0]->break_end?->format('H:i') ?? '') }}">
                        </td>
                    </tr>
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">
                            休憩2
                        </th>
                        <td class="attendance-detail__cell">
                            <input type="time" name="break2_start"
                                value="{{ old('break2_start', $breaks[1]->break_start?->format('H:i') ?? '') }}">
                        </td>
                        <td class="attendance-detail__cell">
                            <input type="time" name="break2_end"
                                value="{{ old('break2_end', $breaks[1]->break_end?->format('H:i') ?? '') }}">
                        </td>
                    </tr>
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">
                            備考
                        </th>
                        <td class="attendance-detail__cell" colspan="2">
                            <textarea class="attendance-detail__textarea" name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="attendance-detail__actions">
                <button class="attendance-detail__button--save" type="submit" name="action" value="update">
                    承認
                </button>
            </div>
        </form>
    </div>
@endsection
