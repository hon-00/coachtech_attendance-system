@extends('layouts.adminapp')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">勤怠詳細</h1>

    @php
        $readonly = $locked ? 'readonly' : '';
        $disabled = $locked ? 'disabled' : '';
    @endphp

    <form class="content-detail" id="attendance-form" action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="POST">
        @csrf
        @method('PUT')

        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

        <div class="content-form__wrap">

            <div class="content-detail__row">
                <label class="content-detail__label">名前</label>
                <p class="content-detail__text">{{ $attendance->user->name }}</p>
            </div>

            <div class="content-detail__row">
                <label class="content-detail__label">日付</label>
                <p class="content-detail__text">
                    <span class="content-detail__text--year">{{ $attendance->work_date->format('Y年') }}</span>
                    <span class="content-detail__text--date">{{ $attendance->work_date->format('m月d日') }}</span>
                </p>
            </div>

            <div class="content-detail__row">
                <label class="content-detail__label">出勤・退勤</label>

                @if($locked)
                    <p class="content-detail__value">
                        {{ $attendance->clock_in?->format('H:i') }}
                        <span class="content-detail__value--time">〜</span>
                        {{ $attendance->clock_out?->format('H:i') }}
                    </p>
                @else
                    <div class="content-detail__input-wrapper">
                        <div class="content-detail__input-group">
                            <input class="content-detail__input" type="text" name="clock_in"  value="{{ old('clock_in',  $attendance->clock_in?->format('H:i')) }}">
                            <span class="content-detail__text--time">～</span>
                            <input class="content-detail__input" type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}">
                        </div>
                    </div>
                @endif
            </div>

            @foreach ($attendance->breakLogs as $i => $break)
            <div class="content-detail__row">
                <label class="content-detail__label">休憩{{ $loop->iteration }}</label>

                @if($locked)
                    <p class="content-detail__value">
                        {{ optional($break->break_start)->format('H:i') }}
                        <span class="content-detail__value--time">〜</span>
                        {{ optional($break->break_end)->format('H:i') }}
                    </p>
                @else
                    <div class="content-detail__input-wrapper">
                        <div class="content-detail__input-group">
                            <input class="content-detail__input" type="text"
                                name="breaks[{{ $i }}][start]"
                                value="{{ old("breaks.$i.start", optional($break->break_start)->format('H:i')) }}">
                            <span class="content-detail__text--time">～</span>
                            <input class="content-detail__input" type="text"
                                name="breaks[{{ $i }}][end]"
                                value="{{ old("breaks.$i.end", optional($break->break_end)->format('H:i')) }}">
                        </div>

                        @error("breaks.$i.start")
                            <p class="content-form__error">{{ $message }}</p>
                        @enderror

                        @error("breaks.$i.end")
                            <p class="content-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                @endif
            </div>
            @endforeach

            <div class="content-detail__row">
                <label class="content-detail__label">備考</label>

                @if($locked)
                    <p class="content-detail__value--note">{{ $attendance->note }}</p>
                @else
                    <textarea class="content-detail__textarea" name="note">{{ old('note', $attendance->note) }}</textarea>
                    @error('note')
                        <p class="content-form__error">{{ $message }}</p>
                    @enderror
                @endif
            </div>
        </div>
    </form>

    @if ($locked)
        <p class="content-detail__alert">
            <span>*</span>承認待ちのため修正はできません。
        </p>
    @endif

    @unless ($locked)
        <div class="content-detail__button">
            <button class="button" type="submit" form="attendance-form">修正</button>
        </div>
    @endunless
</div>
@endsection