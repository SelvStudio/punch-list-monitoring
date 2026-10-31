@extends('layouts.app')

@section('title', 'Notifikasi Internal')
@section('page_title', 'Notifikasi & Pengingat Internal')
@section('page_subtitle', 'Peringatan tenggat waktu, penugasan pekerjaan, dan pembaruan alur verifikasi')

@section('breadcrumb')
    <li class="breadcrumb-item active">Notifikasi</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title font-weight-bold">
                    <i class="far fa-bell mr-1"></i> Daftar Notifikasi Saya 
                    @if($unreadCount > 0)
                        <span class="badge badge-danger ml-1">{{ $unreadCount }} Belum Dibaca</span>
                    @endif
                </h3>
                <div class="card-tools ml-auto">
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.read-all') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-default btn-xs">
                                <i class="fas fa-check-double mr-1"></i> Tandai Semua Sudah Dibaca
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($notifications as $notif)
                        <div class="list-group-item list-group-item-action {{ !$notif->is_read ? 'bg-light font-weight-bold' : '' }} p-3">
                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                <h6 class="mb-1 {{ !$notif->is_read ? 'text-primary font-weight-bold' : 'text-dark' }}">
                                    @if($notif->type === 'overdue')
                                        <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                                    @elseif($notif->type === 'deadline_approaching')
                                        <i class="fas fa-hourglass-half text-warning mr-2"></i>
                                    @elseif($notif->type === 'verification_request')
                                        <i class="fas fa-user-check text-info mr-2"></i>
                                    @elseif($notif->type === 'verification_approved')
                                        <i class="fas fa-check-circle text-success mr-2"></i>
                                    @elseif($notif->type === 'verification_rejected')
                                        <i class="fas fa-times-circle text-danger mr-2"></i>
                                    @else
                                        <i class="fas fa-info-circle text-primary mr-2"></i>
                                    @endif
                                    {{ $notif->title }}
                                </h6>
                                <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-2 text-secondary small" style="font-weight: normal;">
                                {{ $notif->message }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    @if($notif->punchList)
                                        <a href="{{ route('punch-lists.show', $notif->punch_list_id) }}" class="btn btn-xs btn-outline-primary">
                                            Buka Catatan [{{ $notif->punchList->punch_code }}] <i class="fas fa-arrow-right ml-1"></i>
                                        </a>
                                    @endif
                                </div>
                                <div>
                                    @if(!$notif->is_read)
                                        <form action="{{ route('notifications.read', $notif) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-link text-muted">
                                                Tandai Dibaca
                                            </button>
                                        </form>
                                    @else
                                        <span class="badge badge-light border text-muted small"><i class="fas fa-check"></i> Sudah dibaca</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="far fa-bell-slash fa-3x mb-2 text-secondary d-block"></i>
                            Tidak ada notifikasi untuk Anda saat ini.
                        </div>
                    @endforelse
                </div>
            </div>

            @if($notifications->hasPages())
                <div class="card-footer bg-white clearfix">
                    <div class="float-right">
                        {{ $notifications->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
