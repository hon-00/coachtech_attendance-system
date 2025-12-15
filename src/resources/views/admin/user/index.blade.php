@extends('layouts.adminapp')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user/index.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">スタッフ一覧</h1>

    <table class="content-table">
        <colgroup>
            <col class="col-margin">
            <col class="col-name">
            <col class="col-email">
            <col class="col-detail">
            <col class="col-margin">
        </colgroup>
        <thead>
            <tr class="content-table__header">
                <th></th>
                <th class="content-table__name">名前</th>
                <th class="content-table__email">メールアドレス</th>
                <th class="content-table__detail">月次勤怠</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr class="content-table__row">
                <td></td>
                <td class="content-table__cell--name">{{ $user->name }}</td>
                <td class="content-table__cell--email">{{ $user->email }}</td>
                <td class="content-table__cell--detail">
                    <a class="content-table__cell--detail-link" href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}">
                        詳細
                    </a>
                </td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection