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

                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">出勤・退勤</th>
                        @if (is_null($correction) || $correction->status === 'approved')
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="requested_clock_in"
                                    value="{{ old('requested_clock_in', $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '') }}">
                                @error('clock_in_time')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            </td>
                            <td class="attendance-detail__cell">
                                <input class="attendance-detail__time-input" type="time" name="requested_clock_out"
                                    value="{{ old('requested_clock_out', $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '') }}">
                                @error('clock_out_time')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
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

                    {{-- 休憩欄 --}}
                    @foreach ($breaks as $index => $break)
                        <tr class="attendance-detail__row">
                            <th class="attendance-detail__cell attendance-detail__cell--label">休憩{{ $index + 1 }}</th>
                            @if (is_null($correction) || $correction->status === 'approved')
                                <td class="attendance-detail__cell">
                                    <input class="attendance-detail__time-input" type="time"
                                        name="break{{ $index + 1 }}_start"
                                        value="{{ old('break' . ($index + 1) . '_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                                    @error('break' . ($index + 1) . '_start')
                                        <p class="attendance-detail__error">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="attendance-detail__cell">
                                    <input class="attendance-detail__time-input" type="time"
                                        name="break{{ $index + 1 }}_end"
                                        value="{{ old('break' . ($index + 1) . '_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                                    @error('break' . ($index + 1) . '_end')
                                        <p class="attendance-detail__error">{{ $message }}</p>
                                    @enderror
                                </td>
                            @else
                                <td class="attendance-detail__cell">
                                    {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
                                </td>
                                <td class="attendance-detail__cell">
                                    {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
                                </td>
                            @endif
                        </tr>
                    @endforeach

                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">休憩{{ count($breaks) + 1 }}</th>
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

                    <tr class="attendance-detail__row">
                        <th class="attendance-detail__cell attendance-detail__cell--label">備考</th>
                        <td class="attendance-detail__cell" colspan="2">
                            @if (is_null($correction) || $correction->status === 'approved')
                                <textarea class="attendance-detail__textarea" name="reason" rows="3">{{ old('reason', $attendance->reason) }}</textarea>
                                @error('reason')
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            @else
                                <p class="attendance-detail__readonly-text">{{ $attendance->reason ?? '' }}</p>
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
