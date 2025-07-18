@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user/correction-list.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="staff">
        <h1 class="request-list__title">申請一覧</h1>
        <div class="request-list__header">
            <div class="request-list__tabs">
                <a class="request-list__tab {{ request('status', 'pending') === 'pending' ? 'active' : '' }}"
                    href="{{ route('staff.request.list', ['status' => 'pending']) }}">
                    承認待ち
                </a>
                <a href="{{ route('staff.request.list', ['status' => 'approved']) }}"
                    class="request-list__tab {{ request('status') === 'approved' ? 'active' : '' }}">
                    承認済み
                </a>
            </div>
        </div>
        <table class="request-list__table">
            <thead class="request-list__thead">
                <tr class="request-list__row request-list__row--header">
                    <th class="request-list__cell">状態</th>
                    <th class="request-list__cell">名前</th>
                    <th class="request-list__cell">対象日時</th>
                    <th class="request-list__cell">申請理由</th>
                    <th class="request-list__cell">申請日時</th>
                    <th class="request-list__cell">詳細</th>
                </tr>
            </thead>
            <tbody class="request-list__tbody">
                @forelse ($corrections as $correction)
                    <tr class="request-list__row">
                        <td class="request-list__cell">
                            {{ $correction->status === 'pending' ? '承認待ち' : '承認済み' }}
                        </td>
                        <td class="request-list__cell">
                            {{ $correction->user->name ?? '不明なユーザー' }}
                        </td>
                        <td class="request-list__cell">
                            {{ optional($correction->attendance->date)->format('Y/m/d') ?? '-' }}
                        </td>
                        <td class="request-list__cell">
                            {{ $correction->reason ?? '-' }}
                        </td>
                        <td class="request-list__cell">
                            {{ $correction->created_at->format('Y/m/d H:i') }}
                        </td>
                        <td class="request-list__cell">
                            <a class="request-list__link"
                                href="{{ route('staff.attendance.show', $correction->attendance_id) }}">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr class="request-list__row">
                        <td class="request-list__cell" colspan="6">
                            申請が見つかりません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
