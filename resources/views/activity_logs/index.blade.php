@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem')
@section('page_title', 'Log Aktivitas Sistem')
@section('page_subtitle', 'Audit trail pencatatan seluruh mutasi data, perubahan status, dan verifikasi punch list')

@section('breadcrumb')
    <li class="breadcrumb-item active">Log Aktivitas</li>
@endsection

@section('content')
<div class="card card-outline card-secondary shadow-sm mb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Penyaringan Log Aktivitas</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('activity-logs.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">Pengguna:</label>
                        <select name="user_id" class="form-control form-control-sm">
                            <option value="">Semua Pengguna</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ strtoupper($u->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">Jenis Tindakan (Action):</label>
                        <select name="action" class="form-control form-control-sm">
                            <option value="">Semua Tindakan</option>
                            @foreach($actions as $act)
                                <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>
                                    {{ $act }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="small font-weight-bold">Rentang Waktu:</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            <div class="input-group-append"><span class="input-group-text">-</span></div>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-2 pt-4 text-right">
                    <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary btn-sm mr-1">Reset</a>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Saring</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-striped mb-0 text-sm">
            <thead class="thead-light">
                <tr>
                    <th style="width: 170px;">Waktu Kejadian</th>
                    <th>Nama Pengguna</th>
                    <th>Tindakan (Action)</th>
                    <th>Entitas Terkait</th>
                    <th>Deskripsi Aktivitas</th>
                    <th>Alamat IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap text-secondary">
                            <i class="far fa-clock mr-1"></i> {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="font-weight-bold text-dark">
                            @if($log->user)
                                {{ $log->user->name }}
                                <small class="text-muted d-block">({{ strtoupper($log->user->role) }})</small>
                            @else
                                <span class="badge badge-light border">Sistem Otomatis</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ in_array($log->action, ['CREATE', 'LOGIN']) ? 'badge-primary' : (in_array($log->action, ['VERIFY_APPROVE']) ? 'badge-success' : (in_array($log->action, ['VERIFY_REJECT', 'DELETE']) ? 'badge-danger' : 'badge-warning text-dark')) }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td>
                            <small class="badge badge-light border">{{ class_basename($log->entity_type) }} #{{ $log->entity_id ?? '-' }}</small>
                        </td>
                        <td>
                            {{ $log->description }}
                        </td>
                        <td class="text-muted small">{{ $log->ip_address ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Belum ada catatan log aktivitas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="card-footer bg-white clearfix">
            <div class="float-right">
                {{ $logs->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
