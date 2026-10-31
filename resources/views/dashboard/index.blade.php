@extends('layouts.app')

@section('title', 'Dashboard Monitoring')
@section('page_title', 'Dashboard Monitoring Punch List')
@section('page_subtitle', 'Sistem monitoring pekerjaan masa pemeliharaan proyek konstruksi')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<!-- Project Filter Header -->
<div class="card card-outline card-primary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('dashboard') }}" class="form-inline justify-content-between">
            <div class="form-group my-1">
                <label for="project_id" class="mr-2 font-weight-bold"><i class="fas fa-building mr-1"></i> Proyek:</label>
                <select name="project_id" id="project_id" class="form-control form-control-sm" onchange="this.form.submit()">
                    <option value="">Semua Proyek (Total {{ $projects->count() }})</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ $projectId == $p->id ? 'selected' : '' }}>
                            {{ $p->project_code }} - {{ $p->project_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="text-muted small my-1">
                <i class="far fa-clock mr-1"></i> Data dihitung aktual berdasarkan zona waktu <strong>Asia/Jakarta</strong>
            </div>
        </form>
    </div>
</div>

<!-- Summary Metrics Cards (Row 1: 4 Cards) -->
<div class="row">
    <!-- Total Punch List -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalPunchList }}</h3>
                <p class="font-weight-bold mb-0">Total Punch List</p>
                <small>{{ $totalBelumSelesai }} belum selesai</small>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <a href="{{ route('punch-lists.index', array_filter(['project_id' => $projectId])) }}" class="small-box-footer">
                Lihat Semua <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Selesai -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $totalSelesai }}</h3>
                <p class="font-weight-bold mb-0">Punch List Selesai</p>
                <small>Telah terverifikasi tuntas</small>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <a href="{{ route('punch-lists.index', array_filter(['project_id' => $projectId, 'status' => 'Selesai'])) }}" class="small-box-footer">
                Lihat Detail <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Terlambat (Warning / Danger) -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $totalTerlambat }}</h3>
                <p class="font-weight-bold mb-0">Pekerjaan Terlambat</p>
                <small>Melewati batas tenggat</small>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <a href="{{ route('punch-lists.index', array_filter(['project_id' => $projectId, 'filter_overdue' => 1])) }}" class="small-box-footer">
                Periksa Keterlambatan <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Persentase Penyelesaian -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $persentasePenyelesaian }}<sup style="font-size: 20px">%</sup></h3>
                <p class="font-weight-bold mb-0">Tingkat Penyelesaian</p>
                <small>({{ $totalSelesai }}/{{ $totalPunchList }} item)</small>
            </div>
            <div class="icon">
                <i class="fas fa-percentage"></i>
            </div>
            <a href="{{ route('monitoring.index', array_filter(['project_id' => $projectId])) }}" class="small-box-footer">
                Monitoring Proyek <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Summary Row 2: Status Breakdown Badges -->
<div class="row">
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-secondary"><i class="fas fa-plus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Baru</span>
                <span class="info-box-number">{{ $totalBaru }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-primary"><i class="fas fa-user-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Ditugaskan & Proses</span>
                <span class="info-box-number">{{ $totalDitugaskan + $totalDalamProses }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-search-plus"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Menunggu Verifikasi</span>
                <span class="info-box-number">{{ $totalMenungguVerifikasi }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box shadow-sm">
            <span class="info-box-icon bg-danger"><i class="fas fa-fire"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Prioritas Kritis / Tinggi</span>
                <span class="info-box-number">{{ $totalHighCritical }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row">
    <!-- Chart 1: Status Distribution -->
    <div class="col-lg-4 col-md-6">
        <div class="card card-outline card-primary shadow-sm h-100">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-pie mr-1"></i> Distribusi Status</h3>
            </div>
            <div class="card-body">
                <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Priority Distribution -->
    <div class="col-lg-4 col-md-6">
        <div class="card card-outline card-warning shadow-sm h-100">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Distribusi Prioritas</h3>
            </div>
            <div class="card-body">
                <canvas id="priorityChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 3: Monthly Trend -->
    <div class="col-lg-4 col-md-12">
        <div class="card card-outline card-success shadow-sm h-100">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-line mr-1"></i> Tren Temuan vs Selesai</h3>
            </div>
            <div class="card-body">
                <canvas id="trendChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Feeds: Urgent Punch Lists & Recent Punch Lists -->
<div class="row mt-3">
    <!-- Urgent Items -->
    <div class="col-lg-6">
        <div class="card card-danger card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold text-danger">
                    <i class="fas fa-hourglass-half mr-1"></i> Pekerjaan Paling Mendesak & Kritis
                </h3>
                <div class="card-tools">
                    <a href="{{ route('punch-lists.index', ['filter_overdue' => 1]) }}" class="btn btn-tool text-danger">
                        Semua Terlambat <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-valign-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode</th>
                            <th>Judul & Lokasi</th>
                            <th>PIC</th>
                            <th>Tenggat</th>
                            <th>Status Indikator</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($urgentPunchLists as $item)
                            @php $deadline = $item->deadline_status; @endphp
                            <tr>
                                <td class="font-weight-bold">{{ $item->punch_code }}</td>
                                <td>
                                    <div class="font-weight-bold text-truncate" style="max-width: 160px;">{{ $item->title }}</div>
                                    <small class="text-muted text-truncate d-block" style="max-width: 160px;">{{ $item->location_detail ?? '-' }}</small>
                                </td>
                                <td>{{ $item->pic?->name ?? 'Belum Ditugaskan' }}</td>
                                <td>
                                    <div>{{ $item->due_date->format('d/m/Y') }}</div>
                                    <small class="badge {{ $item->priority_badge }}">{{ $item->priority }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $deadline['badge'] }}">{{ $deadline['label'] }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('punch-lists.show', $item) }}" class="btn btn-xs btn-outline-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-check-circle text-success mr-1"></i> Tidak ada pekerjaan terlambat atau mendesak saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Items -->
    <div class="col-lg-6">
        <div class="card card-primary card-outline shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-history mr-1"></i> Catatan Punch List Terbaru
                </h3>
                <div class="card-tools">
                    <a href="{{ route('punch-lists.index') }}" class="btn btn-tool">
                        Lihat Semua <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-striped table-hover table-valign-middle mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode</th>
                            <th>Judul</th>
                            <th>PIC</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPunchLists as $item)
                            <tr>
                                <td class="font-weight-bold">{{ $item->punch_code }}</td>
                                <td>
                                    <div class="font-weight-bold text-truncate" style="max-width: 170px;">{{ $item->title }}</div>
                                    <small class="text-muted">{{ $item->category }}</small>
                                </td>
                                <td>{{ $item->pic?->name ?? 'Belum Ditugaskan' }}</td>
                                <td><span class="badge {{ $item->status_badge }}">{{ $item->status }}</span></td>
                                <td><small>{{ $item->finding_date->format('d/m/Y') }}</small></td>
                                <td>
                                    <a href="{{ route('punch-lists.show', $item) }}" class="btn btn-xs btn-outline-secondary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Belum ada data punch list.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- ChartJS via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    $(function () {
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($statusLabels) !!},
                datasets: [{
                    data: {!! json_encode($statusCounts) !!},
                    backgroundColor: ['#6c757d', '#007bff', '#ffc107', '#17a2b8', '#28a745', '#dc3545'],
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } }
                }
            }
        });

        // Priority Chart
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($priorityLabels) !!},
                datasets: [{
                    label: 'Jumlah Temuan',
                    data: {!! json_encode($priorityCounts) !!},
                    backgroundColor: ['#6c757d', '#17a2b8', '#ffc107', '#dc3545'],
                }]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Trend Chart
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($months) !!},
                datasets: [
                    {
                        label: 'Temuan Dibuat',
                        data: {!! json_encode($findingsPerMonth) !!},
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220,53,69,0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Penyelesaian',
                        data: {!! json_encode($completionsPerMonth) !!},
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40,167,69,0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                maintainAspectRatio: false,
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } }
                }
            }
        });
    });
</script>
@endpush
