@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">勤怠一覧</h1>

    <div class="month-nav">
        <a  class="month-nav__prev" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">前月</a>
        <p class="month-nav__current">{{ $currentTime->format('Y/m') }}</p>
        <a class="month-nav__next" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月</a>
    </div>

    <table class="content-table">
        <thead>
            <tr class="content-table__header">
                <th class="content-table__date">日付</th>
                <th class="content-table__start th-narrow">出勤</th>
                <th class="content-table__end th-narrow">退勤</th>
                <th class="content-table__break th-narrow">休憩</th>
                <th class="content-table__total">合計</th>
                <th class="content-table__detail">詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr class="content-table__row">
                    @php
                    $weekMap = [
                        'Sun' => '日',
                        'Mon' => '月',
                        'Tue' => '火',
                        'Wed' => '水',
                        'Thu' => '木',
                        'Fri' => '金',
                        'Sat' => '土',
                    ];

                    $week = $weekMap[$attendance->work_date->format('D')];
                    @endphp
                <td class="content-table__cell--date">{{ $attendance->work_date->format('m/d') }}({{ $week }})</td>
                <td class="content-table__cell--start">{{ $attendance->clock_in?->format('H:i') ?? '' }}</td>
                <td class="content-table__cell--end">{{ $attendance->clock_out?->format('H:i') ?? '' }}</td>
                <td class="content-table__cell--break">{{ $attendance->formatted_break_total ?: '' }}</td>
                <td class="content-table__cell--total">{{ $attendance->formatted_work_total ?: '' }}</td>
                <td class="content-table__cell--detail">詳細</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection