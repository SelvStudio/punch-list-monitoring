@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('page_title', 'Profil Pengguna Saya')
@section('page_subtitle', 'Kelola informasi identitas dan keamanan kata sandi akun Anda')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profil</li>
@endsection

@section('content')
<div class="row">
    <!-- User Info Card -->
    <div class="col-md-4">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-body box-profile text-center">
                <img class="profile-user-img img-fluid img-circle" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff&size=128" alt="User profile picture">
                <h3 class="profile-username font-weight-bold text-center mt-3">{{ $user->name }}</h3>
                <p class="text-muted text-center mb-2">{{ $user->position ?? 'Staf Proyek' }}</p>

                <div class="text-center mb-3">
                    <span class="badge {{ $user->role === 'admin' ? 'badge-danger' : ($user->role === 'management' ? 'badge-info' : 'badge-success') }} px-3 py-1">
                        {{ strtoupper($user->role) }}
                    </span>
                </div>

                <ul class="list-group list-group-unbordered mb-3 text-left">
                    <li class="list-group-item">
                        <b>Email:</b> <span class="float-right text-dark">{{ $user->email }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Telepon:</b> <span class="float-right text-dark">{{ $user->phone ?? '-' }}</span>
                    </li>
                    <li class="list-group-item">
                        <b>Terakhir Masuk:</b> <span class="float-right text-muted small">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Sesi ini' }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Edit Forms -->
    <div class="col-md-8">
        <!-- Update Info -->
        <div class="card card-outline card-secondary shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-user-edit mr-1"></i> Perbarui Data Diri</h3>
            </div>
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="name" class="font-weight-bold">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone" class="font-weight-bold">Nomor Kontak / WhatsApp</label>
                                <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="position" class="font-weight-bold">Jabatan / Peran Lapangan</label>
                                <input type="text" name="position" id="position" class="form-control" value="{{ old('position', $user->position) }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-right">
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- Update Password -->
        <div class="card card-outline card-danger shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-danger"><i class="fas fa-key mr-1"></i> Ganti Kata Sandi</h3>
            </div>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="current_password" class="font-weight-bold">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                        <input type="password" name="current_password" id="current_password" class="form-control" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="font-weight-bold">Kata Sandi Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation" class="font-weight-bold">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light text-right">
                    <button type="submit" class="btn btn-danger font-weight-bold">
                        <i class="fas fa-lock mr-1"></i> Perbarui Kata Sandi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
