<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH Attendance-system</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <h1 class="header-logo">
                <a class="header-logo__link" href="">
                    <img class="header-logo__img" src="{{ asset('images/logo.svg') }}" alt="Logo" />
                </a>
            </h1>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>