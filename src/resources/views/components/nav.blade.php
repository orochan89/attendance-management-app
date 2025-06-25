<header class="header">
    <div class="header__inner">
        <div class="header__utilities">
            <a href="" class="header__logo">
                <img class="header__logo-image" src="{{ asset('images/logo.svg') }}" alt="COACHTECH">
            </a>
        </div>
        <div class="header__nav-container">
            <nav class="header__nav">
                <ul class="header__nav-list">
                    @if (Auth::check() && Auth::user()->role === 'admin')
                        <li><a href="{{ route('admin.attendance.index') }}" class="header__nav-staff">勤怠一覧</a></li>
                        <li><a href="{{ route('admin.staff.index') }}" class="header__nav-attendance">スタッフ一覧</a></li>
                        <li><a href="{{ route('admin.requests.index') }}" class="header__nav-request">申請一覧</a></li>
                    @elseif (Auth::check() && Auth::user()->role === 'staff')
                        <li><a href="{{ route('staff.attendance.create') }}" class="header__nav-staff">勤怠</a></li>
                        <li><a href="{{ route('staff.attendance.index') }}" class="header__nav-attendance">勤怠一覧</a></li>
                        <li><a href="{{ route('staff.requests.index') }}" class="header__nav-request">申請</a></li>
                    @endif
                    <li>
                        @auth
                            <form class="header__nav-logout" action="{{ route('logout') }}" method="post">
                                @csrf
                                <button class="header__nav-logout-button" type="submit">ログアウト</button>
                            </form>
                        @endauth
                        @guest
                            <form class="header__nav-login" action="{{ route('login') }}" method="get">
                                <button class="header__nav-login-button" type="submit">ログイン</button>
                            </form>
                        @endguest
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</header>
