<header class="header">
    <div class="header__inner">
        <div class="header__utilities">
            <a href="" class="header__logo">
                <img class="header__logo-image" src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </a>
            <div class="header__nav-container">
                <nav class="header__nav">
                    <ul class="header__nav-list">
                        @if (Auth::check() && Auth::user()->role === 'admin')
                            <li class="header__nav-item"><a href="{{ route('admin.attendance.list') }}"
                                    class="header__nav-link">勤怠一覧</a></li>
                            <li class="header__nav-item"><a href="{{ route('admin.staff.list') }}"
                                    class="header__nav-link">スタッフ一覧</a></li>
                            <li class="header__nav-item"><a href="{{ route('admin.request.list') }}"
                                    class="header__nav-link">申請一覧</a></li>
                        @elseif (Auth::check() && Auth::user()->role === 'staff')
                            <li class="header__nav-item"><a href="{{ route('staff.attendance.create') }}"
                                    class="header__nav-link">勤怠</a></li>
                            <li class="header__nav-item"><a href="{{ route('staff.attendance.list') }}"
                                    class="header__nav-link">勤怠一覧</a></li>
                            <li class="header__nav-item"><a href="{{ route('staff.request.list') }}"
                                    class="header__nav-link">申請</a></li>
                        @endif
                        <li class="header__nav-item">
                            @auth
                                <form class="header__nav-logout" action="{{ route('logout') }}" method="post">
                                    @csrf
                                    <button class="header__nav-logout-button" type="submit">ログアウト</button>
                                </form>
                            @endauth
                            @guest
                                <form class="header__nav-login" action="{{ route('login') }}" method="get">
                                    @csrf
                                    <button class="header__nav-login-button" type="submit">ログイン</button>
                                </form>
                            @endguest
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</header>
