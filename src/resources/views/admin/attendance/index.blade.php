@extends('layouts.adminapp')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠</h1>

    <div class="day-nav">
        <a class="day-nav__prev" href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">前日</a>
        <p class="day-nav__current">{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}</p>
        <a class="day-nav__next" href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日</a>
    </div>

    <table class="content-table">
        <thead>
            <tr class="content-table__header">
                <th class="content-table__name">名前</th>
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
                <td class="content-table__cell--name">
                    {{ $attendance->user->name }}
                </td>
                <td class="content-table__cell--start">
                    {{ $attendance->clock_in?->format('H:i') ?? '' }}
                </td>
                <td class="content-table__cell--end">
                    {{ $attendance->clock_out?->format('H:i') ?? '' }}
                </td>
                <td class="content-table__cell--break">
                    {{ $attendance->formatted_break_total ?: '' }}
                </td>
                <td class="content-table__cell--total">
                    {{ $attendance->formatted_work_total ?: '' }}
                </td>
                <td class="content-table__cell--detail">
                    <a class="content-table__cell--detail-link" href="{{ route('admin.attendance.showOrCreate', $attendance->id) }}">
                        詳細
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection