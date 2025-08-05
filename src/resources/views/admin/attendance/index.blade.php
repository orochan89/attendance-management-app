@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance-list.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="admin">
        <h1 class="attendance-list__title">
            {{ $currentDate->format('Y年n月j日') }}の勤怠
        </h1>

        <div class="attendance-list__header">
            <form class="attendance-list__navigation" action="" method="GET">
                <a class="attendance-list__nav-button" href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">
                    前日
                </a>

                <div class="attendance-list__date-wrapper">
                    <i class="fas fa-calendar-alt calendar-icon"></i>
                    <p class="attendance-list__date-text">
                        {{ $currentDate->format('Y/m/d') }}
                    </p>
                </div>

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
                            {{ $attendance['user_name'] }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance['clock_in'] }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance['clock_out'] }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance['break_time'] }}
                        </td>
                        <td class="attendance-list__cell">
                            {{ $attendance['work_time'] }}
                        </td>
                        <td class="attendance-list__cell">
                            <a href="{{ route('admin.attendance.show', $attendance['id']) }}">詳細</a>
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
