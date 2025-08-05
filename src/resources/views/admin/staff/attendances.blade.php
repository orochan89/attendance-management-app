@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff-attendance-list.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="admin">
        <h1 class="attendance-list__title">
            {{ $user->name }}さんの勤怠
        </h1>
        <div class="attendance-list__header">
            <div class="attendance-list__navigation">
                <a class="attendance-list__nav-button"
                    href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $prevMonth]) }}">
                    前月
                </a>

                <div class="attendance-list__month-wrapper">
                    <i class="fas fa-calendar-alt calendar-icon"></i>
                    <p class="attendance-list__month-text">
                        {{ $currentMonth->format('Y/m') }}
                    </p>
                </div>

                <a class="attendance-list__nav-button"
                    href="{{ route('admin.staff.attendance', ['id' => $user->id, 'month' => $nextMonth]) }}">
                    翌月
                </a>
            </div>
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
                @foreach ($dates as $day)
                    <tr class="attendance-list__row">
                        <td class="attendance-list__cell">{{ $day['formatted'] }}</td>
                        <td class="attendance-list__cell">
                            {{ $day['attendance']?->clock_in_time ? \Carbon\Carbon::parse($day['attendance']->clock_in_time)->format('H:i') : '' }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $day['attendance']?->clock_out_time ? \Carbon\Carbon::parse($day['attendance']->clock_out_time)->format('H:i') : '' }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $day['attendance']?->clock_in_time ? $day['attendance']->total_break_formatted : '' }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $day['attendance']?->clock_in_time ? $day['attendance']->work_time_formatted : '' }}
                        </td>
                        <td class="attendance-list__cell">
                            <a
                                href="{{ route('admin.attendance.show', ['id' => $day['attendance'] ? $day['attendance']->id : 'date-' . \Carbon\Carbon::parse($day['date'])->format('Ymd') . '-user-' . $user->id]) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="attendance-list__csv-action">
            <a class="attendance-list__csv-button"
                href="{{ route('admin.staff.attendance.csv', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}">
                CSV出力
            </a>
        </div>
    </div>
@endsection
