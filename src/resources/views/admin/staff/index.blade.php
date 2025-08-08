@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/staff-list.css') }}">
@endsection

@section('content')
    @include('components.nav')

    <div class="admin">
        <h1 class="staff-list__title">スタッフ一覧</h1>
        <table class="staff-list__table">
            <thead class="staff-list__thead">
                <tr class="staff-list__row staff-list__row--header">
                    <th class="staff-list__cell">名前</th>
                    <th class="staff-list__cell">メールアドレス</th>
                    <th class="staff-list__cell">月次勤怠</th>
                </tr>
            </thead>
            <tbody class="staff-list__tbody">
                @forelse ($users as $user)
                    <tr class="staff-list__row">
                        <td class="staff-list__cell staff-list__cell--name">{{ $user->name }}</td>
                        <td class="staff-list__cell staff-list__cell--email">{{ $user->email }}</td>
                        <td class="staff-list__cell staff-list__cell--detail">
                            <a class="staff-list__cell--link" href="{{ route('admin.staff.attendance', $user->id) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr class="staff-list__row">
                        <td class="staff-list__cell" colspan="3">
                            スタッフが見つかりません。
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
