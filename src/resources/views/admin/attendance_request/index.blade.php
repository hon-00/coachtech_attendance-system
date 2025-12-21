@extends('layouts.adminapp')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/index.css') }}">
@endsection

@section('content')
<div class="content">
    <h1 class="content-title">申請一覧</h1>

    <div class="tab-group">
        <ul class="tab-list">
            <li class="tab-item">
                <a href="{{ route('stamp_correction_request.index', ['tab' => 'pending']) }}" class="tab-link {{ $tab === 'pending' ? 'active' : '' }}">
                    承認待ち
                </a>
            </li>
            <li class="tab-item">
                <a href="{{ route('stamp_correction_request.index', ['tab' => 'approved']) }}" class="tab-link {{ $tab === 'approved' ? 'active' : '' }}">
                    承認済み
                </a>
            </li>
        </ul>
    </div>

    <div class="content-item">
        @if($tab === 'pending')
            @if($pendingAdminRequests->isEmpty())
                <p class="content-empty">現在、承認待ちの申請はありません。</p>
            @else
                <table class="content-table">
                    <thead>
                        <tr class="content-table__header">
                            <th class="content-table__status">状態</th>
                            <th class="content-table__name">名前</th>
                            <th class="content-table__date th-wide">対象日時</th>
                            <th class="content-table__note th-wide">申請理由</th>
                            <th class="content-table__created th-wide">申請日時</th>
                            <th class="content-table__detail">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingAdminRequests as $attendanceRequest)
                        <tr class="content-table__row">
                            <td class="content-table__cell--status">{{ $attendanceRequest->status_label }}</td>
                            <td class="content-table__cell--name">{{ $attendanceRequest->user->name }}</td>
                            <td class="content-table__cell--date">
                                {{ $attendanceRequest->attendance->work_date->format('Y/m/d') }}
                            </td>
                            <td class="content-table__cell--note">{{ $attendanceRequest->note }}</td>
                            <td class="content-table__cell--created">
                                {{ $attendanceRequest->created_at->format('Y/m/d') }}
                            </td>
                            <td class="content-table__cell--detail">
                                <a class="content-table__cell--detail-link"
                                    href="{{ route('stamp_correction_request.show', $attendanceRequest) }}">
                                    詳細
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif($tab === 'approved')
            @if($approvedAdminRequests->isEmpty())
                <p class="content-empty">現在、承認済みの申請はありません。</p>
            @else
                <table class="content-table">
                    <thead>
                        <tr class="content-table__header">
                            <th class="content-table__status">状態</th>
                            <th class="content-table__name">名前</th>
                            <th class="content-table__date">対象日</th>
                            <th class="content-table__note">申請理由</th>
                            <th class="content-table__created">申請日時</th>
                            <th class="content-table__detail">詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedAdminRequests as $attendanceRequest)
                        <tr class="content-table__row">
                            <td class="content-table__cell--status">{{ $attendanceRequest->status_label }}</td>
                            <td class="content-table__cell--name">{{ $attendanceRequest->user->name }}</td>
                            <td class="content-table__cell--date">
                                {{ $attendanceRequest->attendance->work_date->format('Y/m/d') }}
                            </td>
                            <td class="content-table__cell--note">{{ $attendanceRequest->note }}</td>
                            <td class="content-table__cell--created">
                                {{ $attendanceRequest->created_at->format('Y/m/d') }}
                            </td>
                            <td class="content-table__cell--detail">
                                <a class="content-table__cell--detail-link"
                                    href="{{ route('stamp_correction_request.show', $attendanceRequest) }}">
                                    詳細
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
</div>
@endsection