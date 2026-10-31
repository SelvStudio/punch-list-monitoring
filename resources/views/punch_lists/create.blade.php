@extends('layouts.app')

@section('title', 'Tambah Punch List Baru')
@section('page_title', 'Tambah Punch List Baru')
@section('page_subtitle', 'Mendaftarkan catatan ketidaksesuaian/temuan pekerjaan konstruksi baru')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('punch-lists.index') }}">Data Punch List</a></li>
    <li class="breadcrumb-item active">Tambah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-edit mr-1"></i> Formulir Pendaftaran Punch List
                </h3>
                <div class="card-tools">
                    <span class="badge badge-info">Nomor Estimasi: {{ $suggestedCode }}</span>
                </div>
            </div>

            <form action="{{ route('punch-lists.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <!-- Project Selection -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_id" class="font-weight-bold">Proyek Konstruksi <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Proyek --</option>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_code }} - {{ $proj->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="font-weight-bold">Kategori Pekerjaan <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                    <option value="Elektrikal" {{ old('category') === 'Elektrikal' ? 'selected' : '' }}>Elektrikal</option>
                                    <option value="Grounding & Proteksi" {{ old('category') === 'Grounding & Proteksi' ? 'selected' : '' }}>Grounding & Proteksi</option>
                                    <option value="Airfield Lighting" {{ old('category') === 'Airfield Lighting' ? 'selected' : '' }}>Airfield Lighting</option>
                                    <option value="Sipil & Struktur" {{ old('category') === 'Sipil & Struktur' ? 'selected' : '' }}>Sipil & Struktur</option>
                                    <option value="Mekanikal / Plumbing" {{ old('category') === 'Mekanikal / Plumbing' ? 'selected' : '' }}>Mekanikal / Plumbing</option>
                                    <option value="Lainnya" {{ old('category') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('category')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="form-group">
                        <label for="title" class="font-weight-bold">Judul Catatan Temuan <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" placeholder="Contoh: Connecting kabel rusak / Kabel BC belum di-couple" value="{{ old('title') }}" required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description" class="font-weight-bold">Deskripsi Rinci Temuan <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" placeholder="Jelaskan kondisi kerusakan, potensi risiko, dan tindakan perbaikan yang direkomendasikan..." required>{{ old('description') }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <!-- Location Detail -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location_detail" class="font-weight-bold">Lokasi Detail Lapangan</label>
                                <input type="text" name="location_detail" id="location_detail" class="form-control @error('location_detail') is-invalid @enderror" placeholder="Contoh: Pit Box Pit-01 Runway Centerline" value="{{ old('location_detail') }}">
                                @error('location_detail')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority" class="font-weight-bold">Tingkat Prioritas <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                    <option value="Rendah" {{ old('priority') === 'Rendah' ? 'selected' : '' }}>Rendah (Dapat diselesaikan bertahap)</option>
                                    <option value="Sedang" {{ old('priority', 'Sedang') === 'Sedang' ? 'selected' : '' }}>Sedang (Penting diselesaikan sesuai jadwal)</option>
                                    <option value="Tinggi" {{ old('priority') === 'Tinggi' ? 'selected' : '' }}>Tinggi (Mempengaruhi fungsi fasilitas)</option>
                                    <option value="Kritis" {{ old('priority') === 'Kritis' ? 'selected' : '' }}>Kritis (Bahaya keselamatan / mendesak)</option>
                                </select>
                                @error('priority')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- PIC Selection -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pic_user_id" class="font-weight-bold">Person in Charge (PIC)</label>
                                <select name="pic_user_id" id="pic_user_id" class="form-control @error('pic_user_id') is-invalid @enderror">
                                    <option value="">-- Belum Ditugaskan --</option>
                                    @foreach($picUsers as $pic)
                                        <option value="{{ $pic->id }}" {{ old('pic_user_id') == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->name }} ({{ $pic->position ?? 'Teknisi' }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Jika PIC dipilih, status otomatis menjadi <em>Ditugaskan</em>.</small>
                                @error('pic_user_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Finding Date -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="finding_date" class="font-weight-bold">Tanggal Temuan <span class="text-danger">*</span></label>
                                <input type="date" name="finding_date" id="finding_date" class="form-control @error('finding_date') is-invalid @enderror" value="{{ old('finding_date', now('Asia/Jakarta')->toDateString()) }}" required>
                                @error('finding_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="due_date" class="font-weight-bold">Tenggat Penyelesaian <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', now('Asia/Jakarta')->addDays(14)->toDateString()) }}" required>
                                <small class="form-text text-muted">Batas waktu pekerjaan harus diverifikasi tuntas.</small>
                                @error('due_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Document Upload (Before Photo) -->
                    <h5 class="font-weight-bold text-secondary mb-3"><i class="fas fa-camera mr-1"></i> Dokumentasi Awal (Foto Sebelum Perbaikan)</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="document_before">Berkas Foto / Dokumen Pendukung</label>
                                <input type="file" name="document_before" id="document_before" class="form-control-file @error('document_before') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="form-text text-muted">Format: JPG, PNG, atau PDF (Maksimal 10 MB).</small>
                                @error('document_before')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="caption_before">Keterangan / Caption Foto</label>
                                <input type="text" name="caption_before" id="caption_before" class="form-control" placeholder="Contoh: Tampak kabel terbakar di dalam box" value="{{ old('caption_before') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('punch-lists.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Batal & Kembali
                    </a>
                    <button type="submit" class="btn btn-primary font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Simpan Catatan Punch List
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
