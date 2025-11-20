@extends('layouts.auth')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">ログイン</h1>

    <form class="content-form" action="{{ route('login') }}"  method="post">
        @csrf

        <div class="form-group">
            <label class="form-item" for="email">メールアドレス</label>
            <input class="form-input" id="email" type="text" name="email" value="{{ old('email') }}">
        </div>
        @if ($errors->has('email'))
        <div class="content-error">
            <ul>
                @foreach ($errors->get('email') as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="form-group">
            <label class="form-item" for="password">パスワード</label>
            <input class="form-input" id="password" type="password" name="password">
        </div>
        @if ($errors->has('password'))
        <div class="content-error">
            {{ $errors->first('password') }}
        </div>
        @endif
        <button class="form-button" type="submit">ログインする</button>
    </form>
    <a class="content-link" href="{{ route('register') }}" >会員登録はこちら</a>
</div>
@endsection