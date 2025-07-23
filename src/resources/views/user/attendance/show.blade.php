@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance-detail.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="staff">
        <h1 class="attendance-detail__title">勤怠詳細</h1>
        <form class="attendance-detail__form"
            action="{{ route('staff.attendance.request_update', ['id' => $attendance->id]) }}" method="POST">
            @csrf
            <table class="attendance-detail__table">
                <tbody class="attendance-detail__tbody">
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">名前</th>
                        <td class="attendance-detail__cell" colspan="2">
                            {{ $attendance->user->name ?? '不明なユーザー' }}
                        </td>
                    </tr>

                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">日付</th>
                        <td class="attendance-detail__cell">{{ $attendance->date->format('Y年') }}</td>
                        <td class="attendance-detail__cell">{{ $attendance->date->format('n月j日') }}</td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">出勤・退勤</th>
                        @if (is_null($correction) || $correction->status === 'approved')
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="clock_in_time"
                                    value="{{ old('clock_in_time', $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '') }}">
                            </td>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="clock_out_time"
                                    value="{{ old('clock_out_time', $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '') }}">
                            </td>
                        @else
                            <td class="attendance-detail__cell">
                                {{ $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '' }}
                            </td>
                            <td class="attendance-detail__cell">
                                {{ $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '' }}
                            </td>
                        @endif
                    </tr>

                    {{-- 休憩1 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">休憩</th>
                        @if (is_null($correction) || $correction->status === 'approved')
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="break1_start"
                                    value="{{ old('break1_start', isset($breaks[0]) && $breaks[0]->break_start ? \Carbon\Carbon::parse($breaks[0]->break_start)->format('H:i') : '') }}">
                            </td>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="break1_end"
                                    value="{{ old('break1_end', isset($breaks[0]) && $breaks[0]->break_end ? \Carbon\Carbon::parse($breaks[0]->break_end)->format('H:i') : '') }}">
                            </td>
                        @else
                            <td class="attendance-detail__cell">
                                {{ isset($breaks[0]) && $breaks[0]->break_start ? \Carbon\Carbon::parse($breaks[0]->break_start)->format('H:i') : '' }}
                            </td>
                            <td class="attendance-detail__cell">
                                {{ isset($breaks[0]) && $breaks[0]->break_end ? \Carbon\Carbon::parse($breaks[0]->break_end)->format('H:i') : '' }}
                            </td>
                        @endif
                    </tr>

                    {{-- 休憩2（送信後に空欄なら非表示） --}}
                    @php
                        $submitted = count(session()->getOldInput()) > 0;
                        $break2Start = old('break2_start');
                        $break2End = old('break2_end');

                        // 休憩2表示条件：送信してない または どちらか入力されている または DBにデータがある
                        $showBreak2 =
                            !$submitted ||
                            $break2Start ||
                            $break2End ||
                            (isset($breaks[1]) && ($breaks[1]->break_start || $breaks[1]->break_end));
                    @endphp

                    @if ($showBreak2)
                        <tr class="attendance-detail__row">
                            <th class="attendance-detail__cell attendance-detail__cell--label">休憩2</th>
                            @if (is_null($correction) || $correction->status === 'approved')
                                <td class="attendance-detail__cell">
                                    <input class="attendance-detail__time-input" type="time" name="break2_start"
                                        value="{{ old('break2_start', isset($breaks[1]) && $breaks[1]->break_start ? \Carbon\Carbon::parse($breaks[1]->break_start)->format('H:i') : '') }}">
                                </td>
                                <td class="attendance-detail__cell">
                                    <input class="attendance-detail__time-input" type="time" name="break2_end"
                                        value="{{ old('break2_end', isset($breaks[1]) && $breaks[1]->break_end ? \Carbon\Carbon::parse($breaks[1]->break_end)->format('H:i') : '') }}">
                                </td>
                            @else
                                <td class="attendance-detail__cell">
                                    {{ isset($breaks[1]) && $breaks[1]->break_start ? \Carbon\Carbon::parse($breaks[1]->break_start)->format('H:i') : '' }}
                                </td>
                                <td class="attendance-detail__cell">
                                    {{ isset($breaks[1]) && $breaks[1]->break_end ? \Carbon\Carbon::parse($breaks[1]->break_end)->format('H:i') : '' }}
                                </td>
                            @endif
                        </tr>
                    @endif

                    {{-- 備考 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">備考</th>
                        <td class="attendance-detail__cell" colspan="2">
                            @if (is_null($correction) || $correction->status === 'approved')
                                <textarea class="attendance-detail__textarea" name="note" rows="3">{{ old('note', $attendance->note) }}</textarea>
                            @else
                                <p class="attendance-detail__readonly-text">{{ $attendance->note ?? '' }}</p>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="attendance-detail__actions">
                @if (is_null($correction) || $correction->status === 'approved')
                    <button class="attendance-detail__button attendance-detail__button--submit" type="submit"
                        name="action" value="edit">
                        修正
                    </button>
                @elseif($correction->status === 'pending')
                    <p class="attendance-detail__message attendance-detail__message--alert">
                        *承認待ちのため修正はできません
                    </p>
                @endif
            </div>
        </form>
    </div>
@endsection
