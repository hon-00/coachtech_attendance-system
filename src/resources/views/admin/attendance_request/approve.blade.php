@extends('layouts.adminapp')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/approve.css') }}">
@endsection

@section('content')
<div class="content">

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="content-title">勤怠詳細</h1>

    <div class="content-detail">

        <div class="content-detail__row">
            <label class="content-detail__label">名前</label>
            <p class="content-detail__value">
                {{ $attendanceRequest->user->name }}
            </p>
        </div>

        <div class="content-detail__row">
            <label class="content-detail__label">日付</label>
            <p class="content-detail__value">
                {{ $attendanceRequest->attendance->work_date->format('Y年m月d日') }}
            </p>
        </div>

        <div class="content-detail__row">
            <label class="content-detail__label">出勤・退勤</label>
            <p class="content-detail__value">
                {{ $attendanceRequest->clock_in ? \Carbon\Carbon::parse($attendanceRequest->clock_in)->format('H:i') : '' }}
                <span class="content-detail__value--time">〜</span>
                {{ $attendanceRequest->clock_out ? \Carbon\Carbon::parse($attendanceRequest->clock_out)->format('H:i') : '' }}
            </p>
        </div>

        @foreach ($attendanceRequest->breaks ?? [] as $break)
            <div class="content-detail__row">
                <label class="content-detail__label">
                    休憩{{ $loop->iteration }}
                </label>

                <p class="content-detail__value">
                    {{ $break['start'] ?? '' }}
                        @if(!empty($break['start']) && !empty($break['end']))
                            <span class="content-detail__value--time">〜</span>
                        @endif
                    {{ $break['end'] ?? '' }}
                </p>
            </div>
        @endforeach

        <div class="content-detail__row">
            <label class="content-detail__label">備考</label>
            <p class="content-detail__value--note">
                {{ $attendanceRequest->note }}
            </p>
        </div>

    </div>

    <div class="content-detail__button">
        @if($attendanceRequest->status === \App\Models\AttendanceRequest::STATUS_PENDING)
            <form method="POST" action="{{ route('admin.attendance_request.approve', $attendanceRequest->id) }}">
                @csrf
                <button type="submit" class="button--approve">
                    承認
                </button>
            </form>
        @else
            <button class="button--approved" disabled>
                承認済み
            </button>
        @endif
    </div>

</div>
@endsection