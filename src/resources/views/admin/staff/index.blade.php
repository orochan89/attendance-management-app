@extends('layouts.app')

@section('css')
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
                        <td class="staff-list__cell">{{ $user->name }}</td>
                        <td class="staff-list__cell">{{ $user->email }}</td>
                        <td class="staff-list__cell">
                            <a class="staff-list__link" href="{{ route('admin.staff.attendances', $user->id) }}">
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
