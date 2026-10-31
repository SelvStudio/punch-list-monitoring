@extends('layouts.app')

@section('title', 'Monitoring Pekerjaan & Masa Pemeliharaan')
@section('page_title', 'Monitoring Pekerjaan & Masa Pemeliharaan')
@section('page_subtitle', 'Memantau sisa masa garansi pemeliharaan, deteksi risiko keterlambatan, dan evaluasi kinerja PIC')

@section('breadcrumb')
    <li class="breadcrumb-item active">Monitoring</li>
@endsection

@section('content')
<!-- Project Selection Card -->
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('monitoring.index') }}" class="form-inline justify-content-between">
            <div class="form-group my-1">
                <label for="project_id" class="mr-2 font-weight-bold"><i class="fas fa-building mr-1"></i> Pilih Proyek Konstruksi:</label>
                <select name="project_id" id="project_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $currentProject && $currentProject->id == $p->id ? 'selected' : '' }}>
                            {{ $p->project_code }} - {{ $p->project_name }} ({{ $p->punch_lists_count }} Item)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="text-muted small my-1">
                <i class="fas fa-shield-alt text-success mr-1"></i> Kontraktor: <strong>{{ $currentProject?->contractor_name }}</strong>
            </div>
        </form>
    </div>
</div>

@if($maintenanceStats)
<!-- Maintenance Period Timeline Banner -->
<div class="card shadow-sm mb-3 border-left-{{ $maintenanceStats['days_remaining'] < 30 ? 'danger' : 'info' }}">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="text-uppercase text-secondary font-weight-bold small">Masa Pemeliharaan Resmi:</div>
                <div class="font-weight-bold text-dark" style="font-size: 1.1rem;">
                    {{ $maintenanceStats['start'] }} &ndash; {{ $maintenanceStats['end'] }}
                </div>
                <div class="text-muted small">Total durasi: {{ $maintenanceStats['total_days'] }} hari kalender</div>
            </div>

            <div class="col-md-6 my-2 my-md-0">
                <div class="d-flex justify-content-between font-weight-bold mb-1 small">
                    <span>Perjalanan Masa Pemeliharaan: {{ $maintenanceStats['days_elapsed'] }} hari berjalan</span>
                    <span class="text-{{ $maintenanceStats['days_remaining'] <= 30 ? 'danger' : 'primary' }}">
                        {{ $maintenanceStats['percent'] }}% Terlewati
                    </span>
                </div>
                <div class="progress progress-sm rounded">
                    <div class="progress-bar bg-{{ $maintenanceStats['days_remaining'] <= 30 ? 'danger' : 'primary' }}" role="progressbar" style="width: {{ $maintenanceStats['percent'] }}%" aria-valuenow="{{ $maintenanceStats['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted mt-1 d-block">
                    Tujuan sistem: Mencegah pekerjaan terbengkalai yang baru terdeteksi setelah masa pemeliharaan berakhir.
                </small>
            </div>

            <div class="col-md-3 text-md-right text-left">
                <div class="p-2 rounded bg-light border d-inline-block text-center">
                    <div class="small font-weight-bold text-muted">SISA WAKTU GARANSI</div>
                    <div class="font-weight-bold text-{{ $maintenanceStats['days_remaining'] <= 30 ? 'danger' : 'success' }}" style="font-size: 1.5rem;">
                        {{ $maintenanceStats['days_remaining'] }} HARI
                    </div>
                    <small class="text-muted">{{ $maintenanceStats['is_ended'] ? 'Masa Pemeliharaan Telah Berakhir' : 'Hingga Serah Terima Akhir' }}</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Risk Alert: Active Overdue & Waiting Verification -->
<div class="row">
    <!-- Active Overdue Items -->
    <div class="col-lg-6">
        <div class="card card-danger card-outline shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-danger">
                    <i class="fas fa-exclamation-triangle mr-1"></i> Punch List Terlambat Aktif ({{ $overdueItems->count() }})
                </h3>
                <span class="badge badge-danger float-right">Kritis</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>No. Punch</th>
                            <th>Judul & Lokasi</th>
                            <th>PIC</th>
                            <th>Tenggat</th>
                            <th>Keterlambatan</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overdueItems as $item)
                            @php $deadline = $item->deadline_status; @endphp
                            <tr>
                                <td class="font-weight-bold"><a href="{{ route('punch-lists.show', $item) }}">{{ $item->punch_code }}</a></td>
                                <td>
                                    <div class="font-weight-bold text-truncate" style="max-width: 140px;">{{ $item->title }}</div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 140px;">{{ $item->location_detail }}</small>
                                </td>
                                <td>{{ $item->pic?->name ?? 'Belum Ditugaskan' }}</td>
                                <td><span class="text-danger font-weight-bold">{{ $item->due_date->format('d/m/Y') }}</span></td>
                                <td><span class="badge badge-danger">{{ $deadline['label'] }}</span></td>
                                <td>
                                    <a href="{{ route('punch-lists.show', $item) }}" class="btn btn-xs btn-outline-danger">Periksa</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle text-success mr-1"></i> Luar biasa! Tidak ada pekerjaan yang terlambat saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Waiting for Verification -->
    <div class="col-lg-6">
        <div class="card card-info card-outline shadow-sm h-100">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-info">
                    <i class="fas fa-clipboard-check mr-1"></i> Menunggu Verifikasi Manajemen ({{ $inVerificationItems->count() }})
                </h3>
                <span class="badge badge-info float-right">Perlu Inspeksi</span>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>No. Punch</th>
                            <th>Judul & Tindakan Perbaikan</th>
                            <th>PIC Lapangan</th>
                            <th>Tenggat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inVerificationItems as $item)
                            <tr>
                                <td class="font-weight-bold"><a href="{{ route('punch-lists.show', $item) }}">{{ $item->punch_code }}</a></td>
                                <td>
                                    <div class="font-weight-bold text-truncate" style="max-width: 160px;">{{ $item->title }}</div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 160px;">{{ $item->resolution_notes ?? 'Menunggu pengecekan fisik' }}</small>
                                </td>
                                <td>{{ $item->pic?->name ?? '-' }}</td>
                                <td><small>{{ $item->due_date->format('d/m/Y') }}</small></td>
                                <td>
                                    <a href="{{ route('punch-lists.show', $item) }}" class="btn btn-xs btn-info">Verifikasi</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    Tidak ada pekerjaan yang sedang menunggu verifikasi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Section: Category Progress Breakdown -->
<div class="card card-outline card-secondary shadow-sm mt-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-layer-group mr-1"></i> Progres Penyelesaian Berdasarkan Kategori Pekerjaan</h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Kategori Pekerjaan</th>
                    <th>Total Temuan</th>
                    <th>Selesai</th>
                    <th>Belum Selesai</th>
                    <th>Terlambat</th>
                    <th style="width: 35%;">Persentase Tuntas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoryBreakdown as $cat)
                    <tr>
                        <td class="font-weight-bold text-dark">{{ $cat['category'] }}</td>
                        <td><span class="badge badge-light border">{{ $cat['total'] }}</span></td>
                        <td><span class="badge badge-success">{{ $cat['completed'] }}</span></td>
                        <td><span class="badge badge-warning text-dark">{{ $cat['in_progress'] }}</span></td>
                        <td>
                            @if($cat['overdue'] > 0)
                                <span class="badge badge-danger">{{ $cat['overdue'] }}</span>
                            @else
                                <span class="badge badge-secondary">0</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="mr-2 font-weight-bold small" style="min-width: 45px;">{{ $cat['percent'] }}%</span>
                                <div class="progress progress-xs flex-grow-1">
                                    <div class="progress-bar bg-success" style="width: {{ $cat['percent'] }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if($picPerformance->count() > 0)
<!-- PIC Performance Evaluation Matrix (Admin & Management) -->
<div class="card card-outline card-warning shadow-sm mt-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold text-dark">
            <i class="fas fa-user-check mr-1"></i> Matriks Beban Kerja & Kinerja Tim Pemeliharaan Lapangan (PIC)
        </h3>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Nama PIC</th>
                    <th>Posisi / Spesialisasi</th>
                    <th>Total Ditugaskan</th>
                    <th>Selesai</th>
                    <th>Tunggakan Aktif</th>
                    <th>Terlambat</th>
                    <th>Tingkat Kinerja</th>
                </tr>
            </thead>
            <tbody>
                @foreach($picPerformance as $p)
                    <tr>
                        <td class="font-weight-bold text-dark">{{ $p['pic']->name }}</td>
                        <td class="text-muted small">{{ $p['pic']->position ?? 'Teknisi Lapangan' }}</td>
                        <td><span class="badge badge-light border">{{ $p['total'] }} item</span></td>
                        <td><span class="badge badge-success">{{ $p['completed'] }} item</span></td>
                        <td><span class="badge badge-warning text-dark">{{ $p['pending'] }} item</span></td>
                        <td>
                            @if($p['overdue'] > 0)
                                <span class="badge badge-danger">{{ $p['overdue'] }} item</span>
                            @else
                                <span class="badge badge-light border">0</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="mr-2 font-weight-bold small" style="min-width: 45px;">{{ $p['rate'] }}%</span>
                                <div class="progress progress-xs flex-grow-1">
                                    <div class="progress-bar {{ $p['rate'] >= 75 ? 'bg-success' : ($p['rate'] >= 40 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ $p['rate'] }}%"></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection
