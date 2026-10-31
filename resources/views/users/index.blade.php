@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Pengguna Sistem')
@section('page_subtitle', 'Pengelolaan akun Administrator, Manajemen Kontraktor, dan Tim Lapangan (PIC)')

@section('breadcrumb')
    <li class="breadcrumb-item active">Manajemen Pengguna</li>
@endsection

@section('content')
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-1"></i> Daftar Akun Pengguna</h3>
        <div class="card-tools ml-auto">
            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus mr-1"></i> Tambah Pengguna Baru
            </a>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('users.index') }}" class="form-inline mb-3">
            <input type="text" name="search" class="form-control form-control-sm mr-2 mb-2" placeholder="Cari nama, email, jabatan..." value="{{ request('search') }}">
            <select name="role" class="form-control form-control-sm mr-2 mb-2">
                <option value="">-- Semua Role --</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                <option value="management" {{ request('role') === 'management' ? 'selected' : '' }}>Manajemen Kontraktor</option>
                <option value="field_team" {{ request('role') === 'field_team' ? 'selected' : '' }}>Tim Pemeliharaan Lapangan</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm mb-2 mr-1">Cari</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm mb-2">Reset</a>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Email</th>
                        <th>Peran (Role)</th>
                        <th>Jabatan / Spesialisasi</th>
                        <th>No. Telepon</th>
                        <th>Tugas Ditangani</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr>
                            <td class="font-weight-bold text-dark">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($u->name) }}&background=e9ecef&color=495057&size=28" class="img-circle mr-2" alt="Avatar">
                                {{ $u->name }}
                                @if($u->id === Auth::id())
                                    <span class="badge badge-light border text-primary ml-1">Saya</span>
                                @endif
                            </td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="badge badge-danger">Administrator</span>
                                @elseif($u->role === 'management')
                                    <span class="badge badge-info">Manajemen Kontraktor</span>
                                @else
                                    <span class="badge badge-success">Tim Lapangan</span>
                                @endif
                            </td>
                            <td>{{ $u->position ?? '-' }}</td>
                            <td>{{ $u->phone ?? '-' }}</td>
                            <td><span class="badge badge-light border">{{ $u->assigned_punch_lists_count }} Item</span></td>
                            <td>
                                @if($u->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('users.edit', $u) }}" class="btn btn-warning btn-xs" title="Edit Akun">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($u->id !== Auth::id())
                                    <form action="{{ route('users.toggle-status', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Ubah status aktif akun {{ $u->name }}?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-{{ $u->is_active ? 'secondary' : 'success' }} btn-xs" title="{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                            <i class="fas fa-{{ $u->is_active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada pengguna yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white clearfix">
            <div class="float-right">
                {{ $users->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
