@extends('layouts.app')

@section('title', 'Detail Punch List: ' . $punchList->punch_code)
@section('page_title', 'Detail Punch List: ' . $punchList->punch_code)
@section('page_subtitle', 'Informasi lengkap temuan, dokumen, riwayat penyelesaian, dan alur verifikasi')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('punch-lists.index') }}">Data Punch List</a></li>
    <li class="breadcrumb-item active">{{ $punchList->punch_code }}</li>
@endsection

@section('content')
@php 
    $deadline = $punchList->deadline_status; 
@endphp

<!-- Main Status Banner -->
<div class="callout {{ $deadline['is_overdue'] ? 'callout-danger' : ($punchList->status === 'Selesai' ? 'callout-success' : 'callout-warning') }} shadow-sm bg-white mb-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <h4 class="font-weight-bold text-dark mb-1">
                <i class="fas fa-clipboard-check text-primary mr-1"></i> [{{ $punchList->punch_code }}] {{ $punchList->title }}
            </h4>
            <div class="text-muted">
                <strong>Proyek:</strong> {{ $punchList->project->project_name }} ({{ $punchList->project->project_code }}) | 
                <strong>Kategori:</strong> <span class="badge badge-light border">{{ $punchList->category }}</span> |
                <strong>Lokasi:</strong> {{ $punchList->location_detail ?? 'Belum ditentukan' }}
            </div>
        </div>
        <div class="text-right mt-2 mt-md-0">
            <span class="badge {{ $punchList->status_badge }} px-3 py-2" style="font-size: 14px;">
                Status: {{ $punchList->status }}
            </span>
            <div class="mt-1">
                <span class="badge {{ $deadline['badge'] }} px-2 py-1">
                    Indikator: {{ $deadline['label'] }}
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: Details & Documentation Gallery -->
    <div class="col-lg-8">
        <!-- Details Card -->
        <div class="card card-outline card-primary shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-1"></i> Deskripsi Temuan & Penugasan</h3>
                <div class="card-tools ml-auto">
                    @if(Auth::user()->hasRole(['admin', 'management']))
                        <a href="{{ route('punch-lists.edit', $punchList) }}" class="btn btn-warning btn-xs">
                            <i class="fas fa-edit mr-1"></i> Edit Data
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small text-uppercase font-weight-bold">Deskripsi Kerusakan / Temuan:</label>
                    <div class="p-3 bg-light rounded text-dark" style="font-size: 1.05rem;">
                        {{ $punchList->description }}
                    </div>
                </div>

                <div class="row border-top pt-3">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold">PENANGGUNG JAWAB (PIC):</small>
                        @if($punchList->pic)
                            <div class="font-weight-bold text-primary">{{ $punchList->pic->name }}</div>
                            <small class="text-muted">{{ $punchList->pic->position ?? 'Teknisi Lapangan' }} ({{ $punchList->pic->email }})</small>
                        @else
                            <span class="badge badge-secondary">Belum Ditugaskan</span>
                        @endif
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold">TINGKAT PRIORITAS:</small>
                        <span class="badge {{ $punchList->priority_badge }} px-2 py-1">{{ $punchList->priority }}</span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold">TANGGAL TEMUAN:</small>
                        <span class="text-dark font-weight-bold">{{ $punchList->finding_date->format('d F Y') }}</span>
                    </div>
                </div>

                <div class="row border-top pt-3">
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold">TENGGAT PENYELESAIAN:</small>
                        <span class="text-danger font-weight-bold">{{ $punchList->due_date->format('d F Y') }}</span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold">TANGGAL SELESAI:</small>
                        <span class="text-success font-weight-bold">
                            {{ $punchList->completed_at ? $punchList->completed_at->format('d F Y H:i') : '-' }}
                        </span>
                    </div>
                    <div class="col-md-4 mb-2">
                        <small class="text-muted d-block font-weight-bold">DIVERIFIKASI OLEH:</small>
                        @if($punchList->verifier)
                            <span class="text-dark font-weight-bold">{{ $punchList->verifier->name }}</span>
                            <small class="text-muted d-block">({{ $punchList->verified_at ? $punchList->verified_at->format('d/m/Y H:i') : '' }})</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>

                @if($punchList->resolution_notes)
                    <div class="mt-3 p-3 rounded {{ $punchList->status === 'Ditolak Saat Verifikasi' ? 'bg-danger-light border border-danger' : 'bg-info-light border border-info' }}" style="background-color: #f8f9fa;">
                        <strong class="{{ $punchList->status === 'Ditolak Saat Verifikasi' ? 'text-danger' : 'text-info' }}">
                            <i class="fas fa-sticky-note mr-1"></i> Catatan Tindak Lanjut / Hasil Verifikasi Terakhir:
                        </strong>
                        <div class="mt-1 text-dark">{{ $punchList->resolution_notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Documentation Card (Before vs After) -->
        <div class="card card-outline card-success shadow-sm mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold"><i class="fas fa-images mr-1"></i> Dokumentasi Lapangan (Bukti Fisik)</h3>
                <div class="card-tools ml-auto">
                    <button type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#uploadDocModal">
                        <i class="fas fa-upload mr-1"></i> Unggah Dokumen Baru
                    </button>
                </div>
            </div>
            <div class="card-body">
                @php
                    $beforeDocs = $punchList->documents->where('document_type', 'Foto Sebelum Perbaikan');
                    $afterDocs = $punchList->documents->where('document_type', 'Foto Setelah Perbaikan');
                    $otherDocs = $punchList->documents->whereNotIn('document_type', ['Foto Sebelum Perbaikan', 'Foto Setelah Perbaikan']);
                @endphp

                <div class="row">
                    <!-- Before Section -->
                    <div class="col-md-6 border-right">
                        <h6 class="font-weight-bold text-danger text-center pb-2 border-bottom">
                            <i class="fas fa-search mr-1"></i> Foto Sebelum Perbaikan (Temuan)
                        </h6>
                        @forelse($beforeDocs as $doc)
                            <div class="card mb-2 shadow-none border">
                                @if($doc->isImage())
                                    <a href="{{ $doc->file_url }}" target="_blank">
                                        <img src="{{ $doc->file_url }}" class="card-img-top img-fluid" style="max-height: 220px; object-fit: cover;" alt="Foto Sebelum">
                                    </a>
                                @else
                                    <div class="p-4 text-center bg-light">
                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        <div class="mt-2 font-weight-bold">{{ $doc->original_filename }}</div>
                                    </div>
                                @endif
                                <div class="card-body p-2 bg-light small">
                                    <div class="font-weight-bold text-dark">{{ $doc->caption ?? 'Tanpa keterangan' }}</div>
                                    <div class="text-muted d-flex justify-content-between mt-1">
                                        <span><i class="far fa-user mr-1"></i> {{ $doc->uploader?->name ?? 'Sistem' }}</span>
                                        <span>{{ $doc->formatted_size }}</span>
                                    </div>
                                    <div class="text-right mt-2">
                                        <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-xs btn-outline-primary">Lihat Ukuran Penuh</a>
                                        @if(Auth::user()->isAdmin() || $doc->uploaded_by === Auth::id())
                                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted bg-light rounded">
                                <i class="fas fa-image fa-2x text-secondary mb-2 d-block"></i>
                                Belum ada foto sebelum perbaikan.
                            </div>
                        @endforelse
                    </div>

                    <!-- After Section -->
                    <div class="col-md-6">
                        <h6 class="font-weight-bold text-success text-center pb-2 border-bottom">
                            <i class="fas fa-check-double mr-1"></i> Foto Setelah Perbaikan (Bukti Tuntas)
                        </h6>
                        @forelse($afterDocs as $doc)
                            <div class="card mb-2 shadow-none border">
                                @if($doc->isImage())
                                    <a href="{{ $doc->file_url }}" target="_blank">
                                        <img src="{{ $doc->file_url }}" class="card-img-top img-fluid" style="max-height: 220px; object-fit: cover;" alt="Foto Setelah">
                                    </a>
                                @else
                                    <div class="p-4 text-center bg-light">
                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        <div class="mt-2 font-weight-bold">{{ $doc->original_filename }}</div>
                                    </div>
                                @endif
                                <div class="card-body p-2 bg-light small">
                                    <div class="font-weight-bold text-dark">{{ $doc->caption ?? 'Tanpa keterangan' }}</div>
                                    <div class="text-muted d-flex justify-content-between mt-1">
                                        <span><i class="far fa-user mr-1"></i> {{ $doc->uploader?->name ?? 'PIC' }}</span>
                                        <span>{{ $doc->formatted_size }}</span>
                                    </div>
                                    <div class="text-right mt-2">
                                        <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-xs btn-outline-success">Lihat Ukuran Penuh</a>
                                        @if(Auth::user()->isAdmin() || $doc->uploaded_by === Auth::id())
                                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted bg-light rounded">
                                <i class="fas fa-image fa-2x text-secondary mb-2 d-block"></i>
                                Belum ada foto bukti setelah perbaikan.
                            </div>
                        @endforelse
                    </div>
                </div>

                @if($otherDocs->count() > 0)
                    <div class="mt-3 pt-3 border-top">
                        <h6 class="font-weight-bold text-secondary mb-2"><i class="fas fa-paperclip mr-1"></i> Dokumen Pendukung Lainnya</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($otherDocs as $doc)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                                    <div>
                                        <i class="fas fa-file-alt text-primary mr-2"></i>
                                        <strong>{{ $doc->original_filename }}</strong> ({{ $doc->document_type }})
                                        <div class="text-muted small">{{ $doc->caption }} - Diunggah oleh {{ $doc->uploader?->name }}</div>
                                    </div>
                                    <div>
                                        <a href="{{ $doc->file_url }}" target="_blank" class="btn btn-xs btn-outline-primary mr-1">Unduh / Buka</a>
                                        @if(Auth::user()->isAdmin() || $doc->uploaded_by === Auth::id())
                                            <form action="{{ route('documents.destroy', $doc) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus dokumen ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <!-- Comments & Progress History -->
        <div class="card card-outline card-secondary shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-comments mr-1"></i> Komunikasi & Tindak Lanjut Pekerjaan</h3>
            </div>
            <div class="card-body">
                <!-- Add Comment Form -->
                <form action="{{ route('punch-lists.comments.store', $punchList) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="form-group mb-2">
                        <textarea name="comment" rows="2" class="form-control" placeholder="Tuliskan catatan kemajuan perbaikan, kendala teknis, atau instruksi..." required></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-paper-plane mr-1"></i> Kirim Catatan Tindak Lanjut
                        </button>
                    </div>
                </form>

                <!-- Comments Timeline -->
                <div class="timeline">
                    @forelse($punchList->comments as $c)
                        <div>
                            <i class="fas fa-comment bg-blue"></i>
                            <div class="timeline-item shadow-none border">
                                <span class="time"><i class="fas fa-clock"></i> {{ $c->created_at->diffForHumans() }}</span>
                                <h3 class="timeline-header font-weight-bold">
                                    <span class="text-primary">{{ $c->user->name }}</span>
                                    <small class="badge badge-light border ml-1">{{ strtoupper($c->user->role) }}</small>
                                </h3>
                                <div class="timeline-body">
                                    {{ $c->comment }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3">
                            Belum ada catatan tindak lanjut pada item ini.
                        </div>
                    @endforelse
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Workflow Actions & Activity Logs -->
    <div class="col-lg-4">
        <!-- Workflow Action Card -->
        <div class="card card-outline card-warning shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-cogs mr-1"></i> Alur Kerja (Workflow)</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-light border small mb-3">
                    <strong>Alur Standar:</strong><br>
                    1. PIC menindaklanjuti &rarr; <em>Dalam Proses</em><br>
                    2. PIC upload bukti &rarr; <em>Ajukan Verifikasi</em><br>
                    3. Manajemen memeriksa &rarr; <em>Setujui (Selesai)</em> atau <em>Tolak Verifikasi</em>.
                </div>

                <!-- Update Status Form -->
                <form action="{{ route('punch-lists.status.update', $punchList) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="form-group">
                        <label for="status" class="font-weight-bold">Pilih Status Baru:</label>
                        <select name="status" id="status" class="form-control" required onchange="toggleWorkflowInputs(this.value)">
                            <option value="">-- Ubah Status --</option>
                            @if(Auth::user()->isFieldTeam())
                                <option value="Dalam Proses" {{ $punchList->status === 'Dalam Proses' ? 'disabled' : '' }}>Dalam Proses Perbaikan</option>
                                <option value="Menunggu Verifikasi">Ajukan Menunggu Verifikasi</option>
                            @else
                                <option value="Dalam Proses">Dalam Proses Perbaikan</option>
                                <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                                <option value="Selesai">Verifikasi Disetujui (Selesai)</option>
                                <option value="Ditolak Saat Verifikasi">Tolak Saat Verifikasi (Kembalikan ke PIC)</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group" id="resolution_notes_group">
                        <label for="resolution_notes" class="font-weight-bold" id="notes_label">Catatan Tindakan / Alasan:</label>
                        <textarea name="resolution_notes" id="resolution_notes" rows="3" class="form-control" placeholder="Tuliskan tindakan perbaikan yang telah dilakukan atau alasan penolakan..."></textarea>
                    </div>

                    <div class="form-group" id="doc_after_group">
                        <label for="document_after" class="font-weight-bold small">Unggah Foto Setelah Perbaikan (Opsional):</label>
                        <input type="file" name="document_after" id="document_after" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf">
                    </div>

                    <button type="submit" class="btn btn-warning btn-block font-weight-bold text-dark">
                        <i class="fas fa-check mr-1"></i> Perbarui Status Pekerjaan
                    </button>
                </form>
            </div>
        </div>

        <!-- Activity Log Card -->
        <div class="card card-outline card-secondary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-1"></i> Log Aktivitas Item Ini</h3>
            </div>
            <div class="card-body p-0" style="max-height: 450px; overflow-y: auto;">
                <ul class="products-list product-list-in-card pl-2 pr-2">
                    @forelse($activityLogs as $log)
                        <li class="item py-2 border-bottom">
                            <div class="product-info ml-2">
                                <span class="badge {{ $log->action === 'CREATE' ? 'badge-primary' : ($log->action === 'VERIFY_APPROVE' ? 'badge-success' : ($log->action === 'VERIFY_REJECT' ? 'badge-danger' : 'badge-info')) }} float-right">
                                    {{ $log->action }}
                                </span>
                                <span class="product-title font-weight-bold text-dark text-truncate d-block" style="max-width: 170px;">
                                    {{ $log->user?->name ?? 'Sistem' }}
                                </span>
                                <span class="product-description text-muted small">
                                    {{ $log->description }}
                                </span>
                                <small class="text-secondary">{{ $log->created_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </li>
                    @empty
                        <li class="item p-3 text-center text-muted">
                            Belum ada riwayat aktivitas.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" role="dialog" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('punch-lists.documents.store', $punchList) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold" id="uploadDocModalLabel"><i class="fas fa-upload mr-1"></i> Unggah Dokumen / Foto Bukti</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="document_type" class="font-weight-bold">Jenis Dokumentasi <span class="text-danger">*</span></label>
                        <select name="document_type" id="document_type" class="form-control" required>
                            <option value="Foto Sebelum Perbaikan">Foto Sebelum Perbaikan</option>
                            <option value="Foto Selama Perbaikan">Foto Selama Perbaikan</option>
                            <option value="Foto Setelah Perbaikan" selected>Foto Setelah Perbaikan</option>
                            <option value="Dokumen Pendukung">Dokumen Pendukung</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="document_file" class="font-weight-bold">Pilih Berkas <span class="text-danger">*</span></label>
                        <input type="file" name="document_file" id="document_file" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf" required>
                        <small class="text-muted">Maksimal ukuran: 10MB (JPG, PNG, PDF)</small>
                    </div>
                    <div class="form-group">
                        <label for="caption" class="font-weight-bold">Keterangan / Caption</label>
                        <input type="text" name="caption" id="caption" class="form-control" placeholder="Contoh: Hasil terminasi kabel setelah penyambungan">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success font-weight-bold">Unggah Sekarang</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleWorkflowInputs(status) {
        const label = document.getElementById('notes_label');
        if (status === 'Ditolak Saat Verifikasi') {
            label.innerHTML = '<span class="text-danger">Alasan Penolakan Verifikasi (Wajib Diisi):</span>';
        } else if (status === 'Menunggu Verifikasi') {
            label.innerHTML = '<span class="text-info">Catatan Tindakan Perbaikan Yang Telah Dilakukan (Wajib):</span>';
        } else if (status === 'Selesai') {
            label.innerHTML = 'Catatan Kelayakan Verifikasi (Opsional):';
        } else {
            label.innerHTML = 'Catatan Tindakan / Progres:';
        }
    }
</script>
@endpush
