@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance-list.css') }}">
@endsection

@section('content')
    @include('components.nav')

    @php
        $currentDate = request('date') ? \Carbon\Carbon::parse(request('date')) : now();
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');
    @endphp

    <div class="admin">
        <h1 class="attendance-list__title">
            {{ $currentDate->format('Y年n月j日') }}の勤怠
        </h1>
        <div class="attendance-list__header">
            <form class="attendance-list__navigation" action="" method="GET">

                <a class="attendance-list__nav-button" href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">
                    前日
                </a>

                <input type="date" name="date" value="{{ $currentDate->format('Y-m-d') }}"
                    class="attendance-list__date-picker" onchange="this.form.submit()">
                <span class="attendance-list__date-display">{{ $currentDate->format('Y年n月j日') }}</span>

                <a class="attendance-list__nav-button" href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">
                    翌日
                </a>
            </form>
        </div>

        <table class="attendance-list__table">
            <thead class="attendance-list__thead">
                <tr class="attendance-list__row attendance-list__row--header">
                    <th class="attendance-list__cell">名前</th>
                    <th class="attendance-list__cell">出勤</th>
                    <th class="attendance-list__cell">退勤</th>
                    <th class="attendance-list__cell">休憩</th>
                    <th class="attendance-list__cell">合計</th>
                    <th class="attendance-list__cell">詳細</th>
                </tr>
            </thead>
            <tbody class="attendance-list__tbody">
                @forelse ($attendances as $attendance)
                    <tr class="attendance-list__row">
                        <td class="attendance-list__cell">
                            {{ $attendance->user->name ?? '不明なユーザー' }}
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
                            <a href="{{ route('admin.attendance.show', $attendance->id) }}">詳細</a>
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
