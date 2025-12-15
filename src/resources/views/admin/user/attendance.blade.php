@extends('layouts.adminapp')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/user/attendance.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">
        {{ $user->name }} さんの勤怠
    </h1>

    <div class="month-nav">
        <a class="month-nav__prev" href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}">
            前月
        </a>

        <p class="month-nav__current">
            {{ \Carbon\Carbon::parse($month)->format('Y/m') }}
        </p>

        <a class="month-nav__next" href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}">
            翌月
        </a>
    </div>

    <table class="content-table">
        <thead>
            <tr class="content-table__header">
                <th class="content-table__date">日付</th>
                <th class="content-table__start">出勤</th>
                <th class="content-table__end">退勤</th>
                <th class="content-table__break">休憩</th>
                <th class="content-table__total">合計</th>
                <th class="content-table__detail">詳細</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($period as $day)
                @php
                    $attendance = $attendances[$day->toDateString()] ?? null;
                @endphp

                <tr class="content-table__row">
                    <td class="content-table__cell--date">
                        {{ $day->format('m/d') }}({{ ['日','月','火','水','木','金','土'][$day->dayOfWeek] }})
                    </td>
                    <td class="content-table__cell--start">
                        {{ $attendance?->clock_in?->format('H:i') ?? '' }}
                    </td>
                    <td class="content-table__cell--end">
                        {{ $attendance?->clock_out?->format('H:i') ?? '' }}
                    </td>
                    <td class="content-table__cell--break">
                        {{ $attendance?->formatted_break_total ?? '' }}
                    </td>
                    <td class="content-table__cell--total">
                        {{ $attendance?->formatted_work_total ?? '' }}
                    </td>
                    <td class="content-table__cell--detail">
                        <a class="content-table__cell--detail-link" href="{{ route('admin.attendance.staff.showOrCreate', [
                            'userId' => $user->id,
                            'date' => $day->format('Y-m-d')
                        ]) }}">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="content-action">
        <a class="button button--csv" href="{{ route('admin.attendance.staff.csv', ['id' => $user->id, 'month' => $month]) }}">
            CSV出力
        </a>
    </div>
</div>
@endsection