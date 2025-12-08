@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">勤怠詳細</h1>

    @php
        $editable = !$attendance->isPending();
        $breakCount = $attendance->breakLogs->count();
    @endphp

    <form class="content-detail" id="attendance-form" action="{{ route('attendance.request.store', ['attendanceId' => $attendance->id]) }}" method="POST">
        @csrf
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
                @if($editable)
                    <div class="content-detail__input-wrapper">
                        <div class="content-detail__input-group">
                            <input class="content-detail__input" type="text" name="clock_in"
                                value="{{ old('clock_in', $attendance->clock_in?->format('H:i')) }}">
                            <span class="content-detail__text--time">～</span>
                            <input class="content-detail__input" type="text" name="clock_out"
                                value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}">
                        </div>
                        @error('clock_in')
                        <p class="content-form__error">{{ $message }}</p>
                        @enderror
                        @error('clock_out')
                        <p class="content-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <p class="content-detail__value">
                        {{ $attendance->clock_in?->format('H:i') }}
                        <span class="content-detail__value--time">〜</span>
                        {{ $attendance->clock_out?->format('H:i') }}
                    </p>
                @endif
            </div>

            @foreach ($attendance->breakLogs as $i => $break)
            <div class="content-detail__row">
                <label class="content-detail__label">休憩{{ $loop->iteration }}</label>
                @if($editable)
                    <div class="content-detail__input-wrapper">
                        <div class="content-detail__input-group">
                            <input class="content-detail__input" type="text" name="breaks[{{ $loop->index }}][start]"
                                value="{{ old('breaks.' . $loop->index . '.start', optional($break->start)->format('H:i')) }}">
                            <span class="content-detail__text--time">～</span>
                            <input class="content-detail__input" type="text" name="breaks[{{ $loop->index }}][end]"
                                value="{{ old('breaks.' . $loop->index . '.end', optional($break->end)->format('H:i')) }}"">
                        </div>
                        @error('breaks.' . $loop->index . '.start')
                            <p class="content-form__error">{{ $message }}</p>
                        @enderror
                        @error('breaks.' . $loop->index . '.end')
                            <p class="content-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <p class="content-detail__value">
                        {{ optional($break->start)->format('H:i') ?? $break->start ?? '' }}
                        <span class="content-detail__value--time">～</span>
                        {{ optional($break->end)->format('H:i') ?? $break->end ?? '' }}
                    </p>
                @endif
            </div>
            @endforeach

            @php
                $showNewBreak = $editable && (
                    old('breaks.new.start') !== null ||
                    old('breaks.new.end') !== null
                );
            @endphp

            @if($showNewBreak)
                <div class="content-detail__row">
                    <label class="content-detail__label">休憩{{ $breakCount + 1 }}</label>
                    <div class="content-detail__input-wrapper">
                        <div class="content-detail__input-group">
                            <input class="content-detail__input" type="text" name="breaks[new][start]"
                                value="{{ old('breaks.new.start') }}">
                            <span class="content-detail__text--time">～</span>
                            <input class="content-detail__input" type="text" name="breaks[new][end]"
                                value="{{ old('breaks.new.end') }}">
                        </div>
                        @error("breaks.new.start")
                            <p class="content-form__error">{{ $message }}</p>
                        @enderror
                        @error("breaks.new.end")
                            <p class="content-form__error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="content-detail__row">
                <label class="content-detail__label">備考</label>
                @if($editable)
                    <textarea class="content-detail__textarea" name="note">{{ old('note', $attendance->note) }}</textarea>
                    @error('note')
                    <p class="content-form__error">{{ $message }}</p>
                    @enderror
                @else
                    <p class="content-detail__value">{{ $attendance->note }}</p>
                @endif
            </div>
        </div>
    </form>
    @if($editable)
    <div class="content-detail__button">
        <button class="button" type="submit" form="attendance-form">修正</button>
    </div>
    @else
        <p class="content-detail__alert">*承認待ちのため修正はできません。</p>
    @endif
</div>
@endsection