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
                        @if (is_null($correction) || optional($correction)->status === 'approved')
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="requested_clock_in"
                                    value="{{ old('requested_clock_in', optional($correction)->requested_clock_in ?? ($attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '')) }}">
                                @error('requested_clock_in')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="requested_clock_out"
                                    value="{{ old('requested_clock_out', optional($correction)->requested_clock_out ?? ($attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '')) }}">
                                @error('requested_clock_out')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            </td>
                        @else
                            <td class="attendance-detail__cell">
                                <p>{{ optional($correction)->requested_clock_in ? \Carbon\Carbon::parse($correction->requested_clock_in)->format('H:i') : '' }}
                                </p>
                            </td>
                            <td class="attendance-detail__cell">
                                <p>{{ optional($correction)->requested_clock_out ? \Carbon\Carbon::parse($correction->requested_clock_out)->format('H:i') : '' }}
                                </p>
                            </td>
                        @endif
                    </tr>

                    {{-- 休憩欄 --}}
                    @foreach ($breaks as $index => $break)
                        @php
                            $startField = 'break' . ($index + 1) . '_start';
                            $endField = 'break' . ($index + 1) . '_end';
                            $correctionBreak = optional(optional($correction)->breakCorrections)[$index] ?? null;
                            $correctionStart = optional($correctionBreak)->requested_break_start;
                            $correctionEnd = optional($correctionBreak)->requested_break_end;
                        @endphp
                        <tr class="attendance-detail__row">
                            <th class="attendance-detail__cell attendance-detail__cell--label">休憩{{ $index + 1 }}</th>
                            @if (is_null($correction) || optional($correction)->status === 'approved')
                                <td class="attendance-detail__cell">
                                    <input class="attendance-detail__time-input" type="time" name="{{ $startField }}"
                                        value="{{ old($startField, $correctionStart ?? ($break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '')) }}">
                                    @error($startField)
                                        <p class="attendance-detail__error">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="attendance-detail__cell">
                                    <input class="attendance-detail__time-input" type="time" name="{{ $endField }}"
                                        value="{{ old($endField, $correctionEnd ?? ($break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '')) }}">
                                    @error($endField)
                                        <p class="attendance-detail__error">{{ $message }}</p>
                                    @enderror
                                </td>
                            @else
                                <td class="attendance-detail__cell">
                                    <p>{{ $correctionStart ? \Carbon\Carbon::parse($correctionStart)->format('H:i') : '' }}
                                    </p>
                                </td>
                                <td class="attendance-detail__cell">
                                    <p>{{ $correctionEnd ? \Carbon\Carbon::parse($correctionEnd)->format('H:i') : '' }}</p>
                                </td>
                            @endif
                        </tr>
                    @endforeach

                    {{-- 追加用の空の休憩フィールド（pending時は非表示） --}}
                    @if (is_null($correction) || optional($correction)->status === 'approved')
                        <tr class="attendance-detail__row">
                            <th class="attendance-detail__cell attendance-detail__cell--label">休憩{{ count($breaks) + 1 }}
                            </th>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time"
                                    name="break{{ count($breaks) + 1 }}_start"
                                    value="{{ old('break' . (count($breaks) + 1) . '_start') }}">
                                @error('break' . (count($breaks) + 1) . '_start')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time"
                                    name="break{{ count($breaks) + 1 }}_end"
                                    value="{{ old('break' . (count($breaks) + 1) . '_end') }}">
                                @error('break' . (count($breaks) + 1) . '_end')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    @endif

                    {{-- 備考 --}}
                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">備考</th>
                        <td class="attendance-detail__cell" colspan="2">
                            @if (is_null($correction) || optional($correction)->status === 'approved')
                                <textarea class="attendance-detail__textarea" name="reason" rows="3">{{ old('reason', optional($correction)->reason ?? '') }}</textarea>
                                @error('reason')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            @else
                                <p class="attendance-detail__readonly-text">{{ optional($correction)->reason ?? '' }}</p>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- ボタン --}}
            <div class="attendance-detail__actions">
                @if (is_null($correction) || optional($correction)->status === 'approved')
                    <button class="attendance-detail__button attendance-detail__button--submit" type="submit"
                        name="action" value="edit">
                        修正
                    </button>
                @elseif(optional($correction)->status === 'pending')
                    <p class="attendance-detail__message attendance-detail__message--alert">
                        *承認待ちのため修正はできません
                    </p>
                @endif
            </div>
        </form>
    </div>
@endsection
