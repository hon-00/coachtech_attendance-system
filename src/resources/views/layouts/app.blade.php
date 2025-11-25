<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <h1 class="header-logo">
                <a class="header-logo__link" href="{{ route('attendance.create') }}">
                    <img class="header-logo__img" src="{{ asset('images/logo.svg') }}" alt="Logo" />
                </a>
            </h1>
        </div>
        <nav>
            <ul class="header-nav">
                <li class="header-nav__item">
                    <p class="header-nav__link">勤怠</p>
                </li>
                <li class="header-nav__item">
                    <p class="header-nav__link">勤怠一覧</p>
                </li>
                <li class="header-nav__item">
                    <p class="header-nav__link">申請</p>
                </li>
                @auth
                    <li class="header-nav__item">
                        <form class="header-nav__form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="header-nav__link">
                                ログアウト
                            </button>
                        </form>
                    </li>
                @endauth
            </ul>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>