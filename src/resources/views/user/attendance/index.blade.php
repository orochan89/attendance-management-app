@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance-list.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="staff">
        <h1 class="attendance-list__title">勤怠一覧</h1>
        <div class="attendance-list__header">
            <form class="attendance-list__navigation" action="" method="GET">
                <a class="attendance-list__nav-button--prev"
                    href="{{ route('staff.attendance.list', ['month' => $prevMonth]) }}">
                    前月
                </a>
                <div class="attendance-list__month-wrapper">
                    <i class="fas fa-calendar-alt calendar-icon"></i>
                    <input type="month" name="month" value="{{ $currentMonth->format('Y-m') }}"
                        class="attendance-list__month-picker" onchange="this.form.submit()">
                </div>
                <a class="attendance-list__nav-button--next"
                    href="{{ route('staff.attendance.list', ['month' => $nextMonth]) }}">
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
                @foreach ($dates as $date)
                    @php
                        $attendance = $attendances->get($date->format('Y-m-d'));
                    @endphp
                    <tr class="attendance-list__row">
                        @php
                            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                        @endphp
                        <td class="attendance-list__cell">
                            {{ $date->format('m/d') }}({{ $weekdays[$date->dayOfWeek] }})
                        </td>
                        <td class="attendance-list__cell">
                            {{ isset($attendance) && $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '' }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ isset($attendance) && $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '' }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance->total_break_formatted }}
                        </td>
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance->work_time_formatted }}
                        </td>
                        <td class="attendance-list__cell">
                            <a href="{{ route('staff.attendance.show', $attendance->id) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
