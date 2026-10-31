@extends('layouts.app')

@section('title', 'Ubah Punch List: ' . $punchList->punch_code)
@section('page_title', 'Ubah Punch List: ' . $punchList->punch_code)
@section('page_subtitle', 'Memperbarui rincian data pekerjaan')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('punch-lists.index') }}">Data Punch List</a></li>
    <li class="breadcrumb-item"><a href="{{ route('punch-lists.show', $punchList) }}">{{ $punchList->punch_code }}</a></li>
    <li class="breadcrumb-item active">Ubah</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card card-outline card-warning shadow-sm">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">
                    <i class="fas fa-edit mr-1"></i> Edit Data Punch List [{{ $punchList->punch_code }}]
                </h3>
            </div>

            <form action="{{ route('punch-lists.update', $punchList) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="project_id" class="font-weight-bold">Proyek Konstruksi <span class="text-danger">*</span></label>
                                <select name="project_id" id="project_id" class="form-control @error('project_id') is-invalid @enderror" required>
                                    @foreach($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ old('project_id', $punchList->project_id) == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_code }} - {{ $proj->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category" class="font-weight-bold">Kategori Pekerjaan <span class="text-danger">*</span></label>
                                <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                    <option value="Elektrikal" {{ old('category', $punchList->category) === 'Elektrikal' ? 'selected' : '' }}>Elektrikal</option>
                                    <option value="Grounding & Proteksi" {{ old('category', $punchList->category) === 'Grounding & Proteksi' ? 'selected' : '' }}>Grounding & Proteksi</option>
                                    <option value="Airfield Lighting" {{ old('category', $punchList->category) === 'Airfield Lighting' ? 'selected' : '' }}>Airfield Lighting</option>
                                    <option value="Sipil & Struktur" {{ old('category', $punchList->category) === 'Sipil & Struktur' ? 'selected' : '' }}>Sipil & Struktur</option>
                                    <option value="Mekanikal / Plumbing" {{ old('category', $punchList->category) === 'Mekanikal / Plumbing' ? 'selected' : '' }}>Mekanikal / Plumbing</option>
                                    <option value="Lainnya" {{ old('category', $punchList->category) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                                </select>
                                @error('category')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title" class="font-weight-bold">Judul Catatan Temuan <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $punchList->title) }}" required>
                        @error('title')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description" class="font-weight-bold">Deskripsi Temuan <span class="text-danger">*</span></label>
                        <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $punchList->description) }}</textarea>
                        @error('description')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="location_detail" class="font-weight-bold">Lokasi Detail Lapangan</label>
                                <input type="text" name="location_detail" id="location_detail" class="form-control @error('location_detail') is-invalid @enderror" value="{{ old('location_detail', $punchList->location_detail) }}">
                                @error('location_detail')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="priority" class="font-weight-bold">Tingkat Prioritas <span class="text-danger">*</span></label>
                                <select name="priority" id="priority" class="form-control @error('priority') is-invalid @enderror" required>
                                    <option value="Rendah" {{ old('priority', $punchList->priority) === 'Rendah' ? 'selected' : '' }}>Rendah</option>
                                    <option value="Sedang" {{ old('priority', $punchList->priority) === 'Sedang' ? 'selected' : '' }}>Sedang</option>
                                    <option value="Tinggi" {{ old('priority', $punchList->priority) === 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                                    <option value="Kritis" {{ old('priority', $punchList->priority) === 'Kritis' ? 'selected' : '' }}>Kritis</option>
                                </select>
                                @error('priority')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="pic_user_id" class="font-weight-bold">Person in Charge (PIC)</label>
                                <select name="pic_user_id" id="pic_user_id" class="form-control @error('pic_user_id') is-invalid @enderror">
                                    <option value="">-- Belum Ditugaskan --</option>
                                    @foreach($picUsers as $pic)
                                        <option value="{{ $pic->id }}" {{ old('pic_user_id', $punchList->pic_user_id) == $pic->id ? 'selected' : '' }}>
                                            {{ $pic->name }} ({{ $pic->position ?? 'Teknisi' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('pic_user_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="finding_date" class="font-weight-bold">Tanggal Temuan <span class="text-danger">*</span></label>
                                <input type="date" name="finding_date" id="finding_date" class="form-control @error('finding_date') is-invalid @enderror" value="{{ old('finding_date', $punchList->finding_date->format('Y-m-d')) }}" required>
                                @error('finding_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="due_date" class="font-weight-bold">Tenggat Penyelesaian <span class="text-danger">*</span></label>
                                <input type="date" name="due_date" id="due_date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $punchList->due_date->format('Y-m-d')) }}" required>
                                @error('due_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light d-flex justify-content-between">
                    <a href="{{ route('punch-lists.show', $punchList) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Batal & Kembali
                    </a>
                    <button type="submit" class="btn btn-warning font-weight-bold text-dark">
                        <i class="fas fa-save mr-1"></i> Perbarui Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
