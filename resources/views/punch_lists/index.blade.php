@extends('layouts.app')

@section('title', 'Daftar Punch List')
@section('page_title', 'Daftar Catatan Punch List')
@section('page_subtitle', 'Memusatkan dan mengelola seluruh pekerjaan punch list proyek')

@section('breadcrumb')
    <li class="breadcrumb-item active">Data Punch List</li>
@endsection

@section('content')
<!-- Filter & Search Card -->
<div class="card card-outline card-secondary shadow-sm mb-3">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-filter mr-1"></i> Pencarian & Penyaringan Lanjutan</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
        </div>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('punch-lists.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">Kata Kunci:</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="No. Punch, Judul, Lokasi..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label class="small font-weight-bold">Proyek:</label>
                        <select name="project_id" class="form-control form-control-sm">
                            <option value="">-- Semua Proyek --</option>
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
                        <label class="small font-weight-bold">Status:</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">-- Semua Status --</option>
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
                            <option value="">-- Semua Prioritas --</option>
                            <option value="Rendah" {{ request('priority') === 'Rendah' ? 'selected' : '' }}>Rendah</option>
                            <option value="Sedang" {{ request('priority') === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="Tinggi" {{ request('priority') === 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                            <option value="Kritis" {{ request('priority') === 'Kritis' ? 'selected' : '' }}>Kritis</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label class="small font-weight-bold">PIC (Penanggung Jawab):</label>
                        <select name="pic_user_id" class="form-control form-control-sm">
                            <option value="">-- Semua PIC --</option>
                            @foreach($picUsers as $pic)
                                <option value="{{ $pic->id }}" {{ request('pic_user_id') == $pic->id ? 'selected' : '' }}>
                                    {{ $pic->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Row 2: Date filters & checkboxes -->
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Tenggat Dari / Sampai:</label>
                        <div class="input-group input-group-sm">
                            <input type="date" name="due_start" class="form-control" value="{{ request('due_start') }}">
                            <div class="input-group-append"><span class="input-group-text">-</span></div>
                            <input type="date" name="due_end" class="form-control" value="{{ request('due_end') }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold">Urutkan Berdasarkan:</label>
                        <div class="input-group input-group-sm">
                            <select name="sort_by" class="form-control">
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Waktu Pembuatan</option>
                                <option value="due_date" {{ request('sort_by') == 'due_date' ? 'selected' : '' }}>Tenggat Waktu</option>
                                <option value="finding_date" {{ request('sort_by') == 'finding_date' ? 'selected' : '' }}>Tanggal Temuan</option>
                                <option value="priority" {{ request('sort_by') == 'priority' ? 'selected' : '' }}>Prioritas</option>
                            </select>
                            <select name="sort_dir" class="form-control" style="max-width: 90px;">
                                <option value="desc" {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Desc</option>
                                <option value="asc" {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Asc</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 pt-3">
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="checkbox" class="custom-control-input" id="filter_overdue" name="filter_overdue" value="1" {{ request('filter_overdue') ? 'checked' : '' }}>
                        <label class="custom-control-label text-danger font-weight-bold" for="filter_overdue">Hanya Yang Terlambat</label>
                    </div>
                    <div class="custom-control custom-checkbox custom-control-inline">
                        <input type="checkbox" class="custom-control-input" id="filter_approaching" name="filter_approaching" value="1" {{ request('filter_approaching') ? 'checked' : '' }}>
                        <label class="custom-control-label text-warning font-weight-bold" for="filter_approaching">Mendekati Tenggat</label>
                    </div>
                </div>

                <div class="col-md-3 text-right pt-3">
                    <a href="{{ route('punch-lists.index') }}" class="btn btn-secondary btn-sm mr-1">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Main Table Card -->
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title font-weight-bold mb-0">
            <i class="fas fa-tasks mr-1"></i> Data Punch List (Total {{ $punchLists->total() }} Catatan)
        </h3>
        <div class="card-tools ml-auto">
            @if(Auth::user()->hasRole(['admin', 'management']))
                <a href="{{ route('punch-lists.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i> Tambah Punch List Baru
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width: 130px;">No. Punch</th>
                    <th>Judul Catatan & Lokasi</th>
                    <th>Kategori</th>
                    <th>PIC</th>
                    <th>Prioritas</th>
                    <th>Tanggal Temuan</th>
                    <th>Tenggat Penyelesaian</th>
                    <th>Status</th>
                    <th>Indikator</th>
                    <th style="width: 120px;" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($punchLists as $item)
                    @php $deadline = $item->deadline_status; @endphp
                    <tr>
                        <td class="font-weight-bold">
                            <a href="{{ route('punch-lists.show', $item) }}" class="text-primary">
                                {{ $item->punch_code }}
                            </a>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $item->title }}</div>
                            <small class="text-muted"><i class="fas fa-map-marker-alt text-secondary mr-1"></i> {{ $item->location_detail ?? '-' }}</small>
                        </td>
                        <td><span class="badge badge-light border">{{ $item->category }}</span></td>
                        <td>
                            @if($item->pic)
                                <div class="font-weight-bold text-dark">{{ $item->pic->name }}</div>
                                <small class="text-muted">{{ $item->pic->position ?? 'PIC Lapangan' }}</small>
                            @else
                                <span class="badge badge-secondary">Belum Ditugaskan</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $item->priority_badge }}">{{ $item->priority }}</span></td>
                        <td><small>{{ $item->finding_date->format('d/m/Y') }}</small></td>
                        <td><small class="font-weight-bold">{{ $item->due_date->format('d/m/Y') }}</small></td>
                        <td><span class="badge {{ $item->status_badge }}">{{ $item->status }}</span></td>
                        <td>
                            <span class="badge {{ $deadline['badge'] }}" style="font-size: 11px;">
                                {{ $deadline['label'] }}
                            </span>
                        </td>
                        <td class="text-center text-nowrap">
                            <a href="{{ route('punch-lists.show', $item) }}" class="btn btn-info btn-xs" title="Lihat Detail">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            @if(Auth::user()->hasRole(['admin', 'management']))
                                <a href="{{ route('punch-lists.edit', $item) }}" class="btn btn-warning btn-xs" title="Edit Data">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endif
                            @if(Auth::user()->isAdmin())
                                <form action="{{ route('punch-lists.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data punch list {{ $item->punch_code }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 text-secondary d-block"></i>
                            <strong>Tidak ada catatan punch list yang sesuai dengan kriteria pencarian.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($punchLists->hasPages())
        <div class="card-footer clearfix bg-white">
            <div class="float-left text-muted small pt-2">
                Menampilkan {{ $punchLists->firstItem() }} sampai {{ $punchLists->lastItem() }} dari total {{ $punchLists->total() }} catatan
            </div>
            <div class="float-right">
                {{ $punchLists->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
