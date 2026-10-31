@extends('layouts.app')

@section('title', 'Pengaturan Sistem')
@section('page_title', 'Pengaturan Sistem & Parameter Monitoring')
@section('page_subtitle', 'Konfigurasi ambang batas peringatan tenggat waktu dan parameter operasional')

@section('breadcrumb')
    <li class="breadcrumb-item active">Pengaturan</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-sliders-h mr-1"></i> Parameter Monitoring</h3>
            </div>
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="deadline_warning_days" class="font-weight-bold">
                            Ambang Batas Peringatan Tenggat (Hari) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" name="deadline_warning_days" id="deadline_warning_days" class="form-control" value="{{ old('deadline_warning_days', $warningDays) }}" min="1" max="30" required>
                            <div class="input-group-append">
                                <span class="input-group-text">Hari Sebelum Tenggat</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Pekerjaan yang memiliki sisa waktu kurang dari atau sama dengan nilai ini akan ditandai dengan badge kuning <strong>Mendekati Tenggat</strong>.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="company_name" class="font-weight-bold">Nama Perusahaan Kontraktor Utama <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name', $companyName) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="app_contact" class="font-weight-bold">Email Dukungan Sistem</label>
                        <input type="email" name="app_contact" id="app_contact" class="form-control" value="{{ old('app_contact', $appContact) }}">
                    </div>
                </div>

                <div class="card-footer bg-light text-right">
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-dark">
                    <i class="fas fa-bolt mr-1"></i> Uji Manual Pemeriksaan Tenggat
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    Sistem memiliki command scheduler <code>punchlist:check-deadlines</code> untuk memeriksa pekerjaan terlambat atau mendekati batas tenggat setiap hari dan mengirim notifikasi internal.
                </p>
                <p class="text-muted small">
                    Anda dapat memicu pemeriksaan ini secara langsung untuk menguji pembuatan notifikasi tanpa menunggu cron scheduler server.
                </p>

                <form action="{{ route('settings.trigger-check') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-block font-weight-bold text-dark">
                        <i class="fas fa-sync-alt mr-1"></i> Jalankan Pemeriksaan Tenggat Sekarang
                    </button>
                </form>
            </div>
        </div>

        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-database mr-1"></i> Status Lingkungan Database</h3>
            </div>
            <div class="card-body small">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td><strong>Koneksi Database:</strong></td>
                        <td>SQLite (Persisten)</td>
                    </tr>
                    <tr>
                        <td><strong>Zona Waktu Sistem:</strong></td>
                        <td>Asia/Jakarta (WIB)</td>
                    </tr>
                    <tr>
                        <td><strong>Bahasa Aplikasi:</strong></td>
                        <td>Bahasa Indonesia (id)</td>
                    </tr>
                    <tr>
                        <td><strong>Template UI:</strong></td>
                        <td>AdminLTE v3.2 + Bootstrap 4</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
