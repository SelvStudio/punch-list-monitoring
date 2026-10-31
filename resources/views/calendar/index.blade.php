@extends('layouts.app')

@section('title', 'Kalender Tenggat Waktu')
@section('page_title', 'Kalender Tenggat Waktu Punch List')
@section('page_subtitle', 'Visualisasi jadwal batas penyelesaian pekerjaan pemeliharaan')

@section('breadcrumb')
    <li class="breadcrumb-item active">Kalender Tenggat</li>
@endsection

@push('styles')
<!-- FullCalendar 5.11 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
<style>
    .fc-event {
        cursor: pointer;
        font-size: 0.85rem;
        padding: 2px 4px;
        border-radius: 4px;
    }
    .fc-header-toolbar {
        margin-bottom: 1.2rem !important;
    }
</style>
@endpush

@section('content')
<!-- Filter Bar -->
<div class="card card-outline card-secondary shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Filter Proyek:</label>
                    <select id="filter_project" class="form-control form-control-sm">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->project_code }} - {{ $p->project_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Filter PIC:</label>
                    <select id="filter_pic" class="form-control form-control-sm">
                        <option value="">Semua PIC</option>
                        @foreach($picUsers as $pic)
                            <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group mb-0">
                    <label class="small font-weight-bold">Filter Status:</label>
                    <select id="filter_status" class="form-control form-control-sm">
                        <option value="">Semua Status</option>
                        <option value="Dalam Proses">Dalam Proses</option>
                        <option value="Menunggu Verifikasi">Menunggu Verifikasi</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4 pt-4 text-md-right">
                <span class="badge badge-danger mr-1"><i class="fas fa-square mr-1"></i> Terlambat</span>
                <span class="badge badge-warning text-dark mr-1"><i class="fas fa-square mr-1"></i> Dalam Proses</span>
                <span class="badge badge-info mr-1"><i class="fas fa-square mr-1"></i> Verifikasi</span>
                <span class="badge badge-success"><i class="fas fa-square mr-1"></i> Selesai</span>
            </div>
        </div>
    </div>
</div>

<!-- Calendar Card -->
<div class="card card-outline card-primary shadow-sm">
    <div class="card-body">
        <div id="calendar"></div>
    </div>
</div>

<!-- Modal Quick Info -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="modalTitle">Detail Tenggat Punch List</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Nomor Punch:</strong> <span id="modalCode"></span></p>
                <p><strong>Proyek:</strong> <span id="modalProject"></span></p>
                <p><strong>PIC:</strong> <span id="modalPic"></span></p>
                <p><strong>Prioritas:</strong> <span id="modalPriority"></span></p>
                <p><strong>Status:</strong> <span id="modalStatus"></span></p>
                <p><strong>Tenggat Waktu:</strong> <span id="modalDue" class="font-weight-bold text-danger"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a href="#" id="modalDetailBtn" class="btn btn-primary font-weight-bold">Buka Halaman Lengkap</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- FullCalendar 5.11 JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/id.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'id',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                list: 'Daftar'
            },
            events: function(info, successCallback, failureCallback) {
                const params = new URLSearchParams({
                    project_id: $('#filter_project').val(),
                    pic_user_id: $('#filter_pic').val(),
                    status: $('#filter_status').val(),
                });

                fetch(`{{ route('calendar.events') }}?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => successCallback(data))
                    .catch(error => failureCallback(error));
            },
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                const props = info.event.extendedProps;
                
                $('#modalTitle').text(info.event.title);
                $('#modalCode').text(props.code);
                $('#modalProject').text(props.project);
                $('#modalPic').text(props.pic);
                $('#modalPriority').text(props.priority);
                $('#modalStatus').text(props.status);
                $('#modalDue').text(info.event.start.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }));
                $('#modalDetailBtn').attr('href', info.event.url);

                $('#eventModal').modal('show');
            }
        });

        calendar.render();

        // Refetch when filters change
        $('#filter_project, #filter_pic, #filter_status').on('change', function() {
            calendar.refetchEvents();
        });
    });
</script>
@endpush
