@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
<div class="content">
    <p class="content-status">
        @switch($attendance->status)
            @case(\App\Models\Attendance::STATUS_NEW)
                勤務外
                @break
            @case(\App\Models\Attendance::STATUS_WORKING)
                出勤中
                @break
            @case(\App\Models\Attendance::STATUS_BREAK)
                休憩中
                @break
            @case(\App\Models\Attendance::STATUS_LEAVE)
                退勤済
                @break
        @endswitch
    </p>

    <p class="content-date">{{ $currentTime->format('Y年m月d日') }}({{ ['日','月','火','水','木','金','土'][$currentTime->dayOfWeek] }})</p>
    <p class="content-time">{{ $currentTime->format('H:i') }}</p>

    @if(session('error'))
        <p class="content-error">{{ session('error') }}</p>
    @endif

    @if($attendance->status === \App\Models\Attendance::STATUS_LEAVE)
        <p class="content-finish">お疲れ様でした。</p>
    @endif

    <div class="content-actions">

        @if($attendance->status === \App\Models\Attendance::STATUS_NEW)
            <form class="content-actions__form" action="{{ route('attendance.clockIn') }}" method="POST">
                @csrf
                <button class="button" type="submit">出勤</button>
            </form>

        @elseif($attendance->status === \App\Models\Attendance::STATUS_WORKING)
            <div class="content-actions__group">
                <form class="content-actions__form" action="{{ route('attendance.clockOut') }}" method="POST">
                    @csrf
                    <button class="button" type="submit">退勤</button>
                </form>

                <form class="content-actions__form" action="{{ route('attendance.breakIn') }}" method="POST">
                    @csrf
                    <button class="button-white" type="submit">休憩入</button>
                </form>
            </div>
        @elseif($attendance->status === \App\Models\Attendance::STATUS_BREAK)
            <form class="content-actions__form" action="{{ route('attendance.breakOut') }}" method="POST">
                @csrf
                <button class="button-white" type="submit">休憩戻</button>
            </form>
        @endif
    </div>
</div>
@endsection