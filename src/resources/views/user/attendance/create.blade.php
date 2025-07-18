@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/attendance-status.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="staff">
        <div class="status">
            @switch($attendance->status)
                @case('off')
                    <div class="status__display">勤務外</div>
                @break

                @case('working')
                    <div class="status__display">出勤中</div>
                @break

                @case('break')
                    <div class="status__display">休憩中</div>
                @break

                @case('done')
                    <div class="status__display">退勤済</div>
                @break

                @default
                    <div class="status__display">不明なステータス</div>
            @endswitch
        </div>
        <div class="status__datetime status__datetime--date current-date"></div>
        <div class="status__datetime status__datetime--time current-time"></div>

        <div class="status__actions">
            <form class="status__form" action="{{ route('staff.attendance.action') }}" method="POST">
                @csrf
                @switch($attendance->status)
                    @case('off')
                        <button class="status__form-button status__form-button--start" type="submit" name="action"
                            value="start_work">
                            出勤
                        </button>
                    @break

                    @case('working')
                        <button class="status__form-button status__form-button--end" type="submit" name="action" value="end_work">
                            退勤
                        </button>
                        <button class="status__form-button status__form-button--break" type="submit" name="action"
                            value="start_break">
                            休憩入
                        </button>
                    @break

                    @case('break')
                        <button class="status__form-button status__form-button--resume" type="submit" name="action"
                            value="resume_work">
                            休憩戻
                        </button>
                    @break

                    @case('done')
                        <p class="status__form-message">お疲れ様でした。</p>
                    @break

                    @default
                        <p class="status__form-message">不明なステータス</p>
                @endswitch
            </form>
        </div>
    </div>
@endsection

@section('js')
    <script>
        function updateDateTime() {
            const now = new Date();

            const days = ['日', '月', '火', '水', '木', '金', '土'];
            const year = now.getFullYear();
            const month = now.getMonth() + 1;
            const date = now.getDate();
            const day = days[now.getDay()];
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');

            const formattedDate = `${year}年${month}月${date}日(${day})`;
            const formattedTime = `${hours}:${minutes}`;

            document.querySelectorAll('.current-date').forEach(el => el.textContent = formattedDate);
            document.querySelectorAll('.current-time').forEach(el => el.textContent = formattedTime);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);
    </script>
@endsection
