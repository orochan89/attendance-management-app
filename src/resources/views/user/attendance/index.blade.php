@extends('layouts.app')

@section('css')
@endsection

@section('content')
    @include('components.nav')

    <div class="staff">
        <h1 class="attendance-list__title">勤怠一覧</h1>
        <div class="attendance-list__header">
            <form class="attendance-list__navigation" action="" method="GET">
                @php
                    $currentMonth = request('month') ? \Carbon\Carbon::parse(request('month')) : now();
                    $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
                    $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
                @endphp

                <a class="attendance-list__nav-button" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">
                    前月
                </a>

                <input type="month" name="month" value="{{ $currentMonth->format('Y-m') }}"
                    class="attendance-list__month-picker" onchange="this.form.submit()">
                <span class="attendance-list__month-display">{{ $currentMonth->format('Y/m') }}</span>

                <a class="attendance-list__nav-button" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">
                    翌月
                </a>
            </form>
        </div>
        <table class="attendance-list__table">
            <thead class="attendance-list__thead">
                <tr class="attendance-list__row attendance-list__row--header">
                    <th class="attendance-list__cell">日付</th>
                    <th class="attendance-list__cell">出勤</th>
                    <th class="attendance-list__cell">退勤</th>
                    <th class="attendance-list__cell">休憩</th>
                    <th class="attendance-list__cell">合計</th>
                    <th class="attendance-list__cell">詳細</th>
                </tr>
            </thead>
            <tbody class="attendance-list__tbody">
                @forelse ($attendance as $attendance)
                    <tr class="attendance-list__row">
                        <td class="attendance-list__cell">
                            {{ \Carbon\Carbon::parse($attendance->date)->format('m/d(D)') }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '-' }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '-' }}
                        </td>
                        <td class="attendance-list__cell">
                            @php
                                $totalBreakMinutes = $attendance->breaks->reduce(function ($carry, $break) {
                                    if ($break->break_start && $break->break_end) {
                                        $start = \Carbon\Carbon::parse($break->break_start);
                                        $end = \Carbon\Carbon::parse($break->break_end);
                                        return $carry + $start->diffInMinutes($end);
                                    }
                                    return $carry;
                                }, 0);
                                $breakFormatted = sprintf(
                                    '%d:%02d',
                                    floor($totalBreakMinutes / 60),
                                    $totalBreakMinutes % 60,
                                );
                            @endphp
                            {{ $totalBreakMinutes > 0 ? $breakFormatted : '-' }}
                        </td>
                        <td class="attendance-list__cell">
                            @php
                                if ($attendance->clock_in_time && $attendance->clock_out_time) {
                                    $start = \Carbon\Carbon::parse($attendance->clock_in_time);
                                    $end = \Carbon\Carbon::parse($attendance->clock_out_time);
                                    $workMinutes = $start->diffInMinutes($end) - $totalBreakMinutes;
                                    $workFormatted = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                                } else {
                                    $workFormatted = '-';
                                }
                            @endphp
                            {{ $workFormatted }}
                        </td>
                        <td class="attendance-list__cell">
                            <a href="{{ route('attendance.show', $attendance->id) }}">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr class="attendance-list__row">
                        <td class="attendance-list__cell" colspan="6">記録がありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
