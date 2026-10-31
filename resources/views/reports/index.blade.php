@extends('layouts.app')

@section('title', 'Laporan Monitoring Punch List')
@section('page_title', 'Laporan & Ekspor Monitoring')
@section('page_subtitle', 'Rekapitulasi berkala pekerjaan punch list untuk evaluasi manajemen')

@section('breadcrumb')
    <li class="breadcrumb-item active">Laporan</li>
@endsection

@section('content')
<!-- Filter Card -->
<div class="card card-outline card-primary shadow-sm mb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Parameter Penyaringan Laporan</h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('reports.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">Proyek:</label>
                        <select name="project_id" class="form-control form-control-sm">
                            <option value="">Semua Proyek</option>
                            @foreach($projects as $p)
                                <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                                    {{ $p->project_code }} - {{ $p->project_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">PIC (Teknisi):</label>
                        <select name="pic_user_id" class="form-control form-control-sm">
                            <option value="">Semua PIC</option>
                            @foreach($picUsers as $pic)
                                <option value="{{ $pic->id }}" {{ request('pic_user_id') == $pic->id ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Status:</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Semua Status</option>
                            <option value="Baru" {{ request('status') === 'Baru' ? 'selected' : '' }}>Baru</option>
                            <option value="Ditugaskan" {{ request('status') === 'Ditugaskan' ? 'selected' : '' }}>Ditugaskan</option>
                            <option value="Dalam Proses" {{ request('status') === 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
                            <option value="Menunggu Verifikasi" {{ request('status') === 'Menunggu Verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="Selesai" {{ request('status') === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="Ditolak Saat Verifikasi" {{ request('status') === 'Ditolak Saat Verifikasi' ? 'selected' : '' }}>Ditolak Saat Verifikasi</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">Prioritas:</label>
                        <select name="priority" class="form-control form-control-sm">
                            <option value="">Semua Prioritas</option>
                            <option value="Rendah" {{ request('priority') === 'Rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="Sedang" {{ request('priority') === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="Tinggi" {{ request('priority') === 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                            <option value="Kritis" {{ request('priority') === 'Kritis' ? 'selected' : '' }}>Kritis</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">Rentang Temuan (Dari - Sampai):</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            <div class="input-group-append"><span class="input-group-text">-</span></div>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="checkbox" class="custom-control-input" id="rep_overdue" name="filter_overdue" value="1" {{ request('filter_overdue') ? 'checked' : '' }}>
                        <label class="custom-control-label text-danger font-weight-bold" for="rep_overdue">Hanya Yang Terlambat</label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="checkbox" class="custom-control-input" id="rep_completed" name="filter_completed" value="1" {{ request('filter_completed') ? 'checked' : '' }}>
                        <label class="custom-control-label text-success font-weight-bold" for="rep_completed">Hanya Yang Selesai</label>
                    </div>
                </div>

                <div class="col-md-6 text-right">
                    <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm mr-1">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search"></i> Tampilkan
                    </button>
                    <!-- Export CSV with same query params -->
                    <a href="{{ route('reports.export-csv', request()->query()) }}" class="btn btn-success btn-sm mr-1">
                        <i class="fas fa-file-excel mr-1"></i> Ekspor CSV / Excel
                    </a>
                    <!-- Print View -->
                    <a href="{{ route('reports.print', request()->query()) }}" target="_blank" class="btn btn-info btn-sm">
                        <i class="fas fa-print mr-1"></i> Versi Cetak
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Badges of Filtered Results -->
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-light shadow-sm">
            <span class="info-box-icon bg-info"><i class="fas fa-list-ol"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Hasil Filter</span>
                <span class="info-box-number">{{ $totalCount }} Catatan</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-light shadow-sm">
            <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Telah Diselesaikan</span>
                <span class="info-box-number">{{ $completedCount }} Catatan</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-light shadow-sm">
            <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tercatat Terlambat</span>
                <span class="info-box-number">{{ $overdueCount }} Catatan</span>
            </div>
        </div>
    </div>
</div>

<!-- Report Table Card -->
<div class="card card-outline card-secondary shadow-sm">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-bordered table-striped mb-0 text-sm">
            <thead class="thead-light">
                <tr>
                    <th style="width: 40px;">No.</th>
                    <th style="width: 120px;">No. Punch</th>
                    <th>Judul Catatan & Lokasi</th>
                    <th>Proyek</th>
                    <th>PIC</th>
                    <th>Prioritas</th>
                    <th>Tanggal Temuan</th>
                    <th>Tenggat</th>
                    <th>Status</th>
                    <th>Tanggal Selesai</th>
                    <th>Durasi</th>
                    <th>Keterangan Keterlambatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($punchLists as $idx => $item)
                    @php
                        $deadline = $item->deadline_status;
                        $duration = '-';
                        if ($item->completed_at) {
                            $fDate = \Carbon\Carbon::parse($item->finding_date);
                            $cDate = \Carbon\Carbon::parse($item->completed_at);
                            $duration = $fDate->diffInDays($cDate) . ' hari';
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $punchLists->firstItem() + $idx }}</td>
                        <td class="font-weight-bold">
                            <a href="{{ route('punch-lists.show', $item) }}">{{ $item->punch_code }}</a>
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $item->title }}</div>
                            <small class="text-muted">{{ $item->location_detail ?? '-' }}</small>
                        </td>
                        <td><small>{{ $item->project->project_name }}</small></td>
                        <td>{{ $item->pic?->name ?? 'Belum Ditugaskan' }}</td>
                        <td><span class="badge {{ $item->priority_badge }}">{{ $item->priority }}</span></td>
                        <td>{{ $item->finding_date->format('d/m/Y') }}</td>
                        <td><strong class="text-dark">{{ $item->due_date->format('d/m/Y') }}</strong></td>
                        <td><span class="badge {{ $item->status_badge }}">{{ $item->status }}</span></td>
                        <td>{{ $item->completed_at ? $item->completed_at->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $duration }}</td>
                        <td>
                            <span class="badge {{ $deadline['badge'] }}">{{ $deadline['label'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="text-center py-4 text-muted">
                            Tidak ada data untuk filter laporan yang dipilih.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($punchLists->hasPages())
        <div class="card-footer bg-white clearfix">
            <div class="float-right">
                {{ $punchLists->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
