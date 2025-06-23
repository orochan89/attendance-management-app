<header class="header">
    <div class="header__inner">
        <div class="header__utilities">
            <a href="" class="header__logo">
                <img class="header__logo__image" src="{{ asset('materials/logo.svg') }}" alt="COACHTECH">
            </a>
        </div>
        <div class="header-nav-container">
            <nav class="header-nav">
                <li><a href="" class="header-nav__staff"></a></li>
                <li><a href="" class="header-nav__attendance"></a></li>
                <li></li>
                <li>
                    @auth
                        <form class="header-nav__logout" action="{{ route('logout') }}" method="post">
                            @csrf
                            <button class="header-nav__logout__button" type="submit">ログアウト</button>
                        </form>
                    @endauth
                    @guest
                        <form class="header-nav__login" action="{{ route('login') }}" method="get">
                            <button class="header-nav__login__button" type="submit">ログイン</button>
                        </form>
                    @endguest
                </li>
            </nav>
        </div>
    </div>
</header>
