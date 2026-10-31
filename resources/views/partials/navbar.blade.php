<nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 shadow-sm">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('dashboard') }}" class="nav-link font-weight-bold text-dark">
                <i class="fas fa-clipboard-check text-primary mr-1"></i> Punch List Monitoring System
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Notifications Dropdown Menu -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                @if (isset($navUnreadCount) && $navUnreadCount > 0)
                    <span class="badge badge-danger navbar-badge">{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow">
                <span class="dropdown-item dropdown-header font-weight-bold">
                    {{ $navUnreadCount ?? 0 }} Notifikasi Belum Dibaca
                </span>
                <div class="dropdown-divider"></div>
                @if (isset($navNotifications) && $navNotifications->count() > 0)
                    @foreach ($navNotifications as $notif)
                        <form action="{{ route('notifications.read', $notif) }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-wrap border-bottom py-2 text-left" style="background: none; border: none; width: 100%;">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-circle text-warning mr-2"></i>
                                    <div style="font-size: 0.85rem;">
                                        <strong>{{ $notif->title }}</strong>
                                        <div class="text-muted small text-truncate" style="max-width: 230px;">
                                            {{ $notif->message }}
                                        </div>
                                        <span class="text-muted" style="font-size: 0.75rem;">{{ $notif->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </button>
                        </form>
                    @endforeach
                @else
                    <div class="dropdown-item text-center text-muted py-3">
                        <i class="fas fa-check-circle text-success mb-1"></i>
                        <div>Tidak ada notifikasi baru</div>
                    </div>
                @endif
                <div class="dropdown-divider"></div>
                <a href="{{ route('notifications.index') }}" class="dropdown-item dropdown-footer text-primary font-weight-bold">
                    Lihat Semua Notifikasi
                </a>
            </div>
        </li>

        <!-- User Dropdown Menu -->
        <li class="nav-item dropdown user-menu">
            <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff" class="user-image img-circle elevation-1" alt="User Image">
                <span class="d-none d-md-inline font-weight-bold">{{ Auth::user()->name }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow">
                <!-- User image -->
                <li class="user-header bg-primary">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=fff&color=0D8ABC" class="img-circle elevation-2" alt="User Image">
                    <p class="font-weight-bold mb-0">
                        {{ Auth::user()->name }}
                    </p>
                    <small>
                        <span class="badge badge-light text-dark">
                            @if(Auth::user()->role === 'admin')
                                Administrator Sistem
                            @elseif(Auth::user()->role === 'management')
                                Manajemen Kontraktor
                            @else
                                Tim Pemeliharaan Lapangan (PIC)
                            @endif
                        </span>
                    </small>
                </li>
                <!-- Menu Body -->
                <li class="user-body py-2">
                    <div class="text-muted small">
                        <i class="fas fa-envelope mr-1"></i> {{ Auth::user()->email }}<br>
                        <i class="fas fa-id-badge mr-1"></i> {{ Auth::user()->position ?? 'Staf Proyek' }}
                    </div>
                </li>
                <!-- Menu Footer-->
                <li class="user-footer d-flex justify-content-between">
                    <a href="{{ route('profile') }}" class="btn btn-default btn-sm btn-flat">
                        <i class="fas fa-user-cog mr-1"></i> Profil
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger btn-sm btn-flat">
                            <i class="fas fa-sign-out-alt mr-1"></i> Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
