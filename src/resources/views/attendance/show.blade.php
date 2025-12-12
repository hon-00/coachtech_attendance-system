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
                <p class="content-detail__value">{{ $attendance->user->name }}</p>
            </div>

            <div class="content-detail__row">
                <label class="content-detail__label">日付</label>
                <p class="content-detail__value">
                    <span class="content-detail__value--year">{{ $attendance->work_date->format('Y年') }}</span>
                    <span class="content-detail__value--date">{{ $attendance->work_date->format('m月d日') }}</span>
                </p>
            </div>

            <div class="content-detail__row">
                <label class="content-detail__label">出勤・退勤</label>

                @if($editable)
                    <div class="content-detail__input-wrapper">
                        <div class="content-detail__input-group">
                            <input class="content-detail__input" type="text" name="clock_in" value="{{ old('clock_in', $attendance->clock_in?->format('H:i')) }}">
                            <span class="content-detail__value--time">〜</span>
                            <input class="content-detail__input" type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}">
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
                                <input class="content-detail__input" type="text" name="breaks[{{ $loop->index }}][break_start]" value="{{ old('breaks.' . $loop->index . '.break_start', optional($break->break_start)->format('H:i')) }}">
                                <span class="content-detail__value--time">〜</span>
                                <input class="content-detail__input" type="text" name="breaks[{{ $loop->index }}][break_end]" value="{{ old('breaks.' . $loop->index . '.break_end', optional($break->break_end)->format('H:i')) }}">
                            </div>

                            @error("breaks.$loop->index.break_start")
                                <p class="content-form__error">{{ $message }}</p>
                            @enderror
                            @error("breaks.$loop->index.break_end")
                                <p class="content-form__error">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <p class="content-detail__value">
                            {{ optional($break->break_start)->format('H:i') }}
                            <span class="content-detail__value--time">〜</span>
                            {{ optional($break->break_end)->format('H:i') }}
                        </p>
                    @endif
                </div>
            @endforeach

            @if($editable)
                <div class="content-detail__row">
                    <label class="content-detail__label">休憩{{ $breakCount + 1 }}</label>
                    <div class="content-detail__input-wrapper">
                        <div class="content-detail__input-group">
                            <input class="content-detail__input" type="text" name="breaks[new][break_start]" value="{{ old('breaks.new.break_start') }}">
                            <span class="content-detail__value--time">〜</span>
                            <input class="content-detail__input" type="text" name="breaks[new][break_end]" value="{{ old('breaks.new.break_end') }}">
                        </div>

                        @error("breaks.new.break_start")
                            <p class="content-form__error">{{ $message }}</p>
                        @enderror
                        @error("breaks.new.break_end")
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
        <p class="content-detail__alert"><span>*</span>承認待ちのため修正できません。</p>
    @endif
</div>
@endsection