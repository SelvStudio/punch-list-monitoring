<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('dashboard') }}" class="brand-link">
        <span class="brand-image img-circle elevation-3 d-flex align-items-center justify-content-center bg-warning text-dark font-weight-bold" style="width: 33px; height: 33px; font-size: 16px;">
            <i class="fas fa-hard-hat"></i>
        </span>
        <span class="brand-text font-weight-bold ml-2" style="font-size: 0.95rem;">Punch List MS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="image">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=6c757d&color=fff" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="{{ route('profile') }}" class="d-block font-weight-bold text-truncate" style="max-width: 160px;">
                    {{ Auth::user()->name }}
                </a>
                <span class="badge {{ Auth::user()->role === 'admin' ? 'badge-danger' : (Auth::user()->role === 'management' ? 'badge-info' : 'badge-success') }}" style="font-size: 10px;">
                    {{ strtoupper(Auth::user()->role) }}
                </span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Data Punch List -->
                <li class="nav-item">
                    <a href="{{ route('punch-lists.index') }}" class="nav-link {{ request()->routeIs('punch-lists.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-clipboard-list"></i>
                        <p>Data Punch List</p>
                    </a>
                </li>

                <!-- Tambah Punch List (Admin & Management) -->
                @if (Auth::user()->hasRole(['admin', 'management']))
                    <li class="nav-item">
                        <a href="{{ route('punch-lists.create') }}" class="nav-link {{ request()->routeIs('punch-lists.create') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-plus-circle text-info"></i>
                            <p>Tambah Punch List</p>
                        </a>
                    </li>
                @endif

                <!-- Monitoring Pekerjaan -->
                <li class="nav-item">
                    <a href="{{ route('monitoring.index') }}" class="nav-link {{ request()->routeIs('monitoring.index') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-chart-line text-warning"></i>
                        <p>Monitoring Pekerjaan</p>
                    </a>
                </li>

                <!-- Kalender Tenggat Waktu -->
                <li class="nav-item">
                    <a href="{{ route('calendar.index') }}" class="nav-link {{ request()->routeIs('calendar.index') ? 'active' : '' }}">
                        <i class="nav-icon far fa-calendar-alt text-success"></i>
                        <p>Kalender Tenggat Waktu</p>
                    </a>
                </li>

                <!-- Notifikasi -->
                <li class="nav-item">
                    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="nav-icon far fa-bell"></i>
                        <p>
                            Notifikasi
                            @if (isset($navUnreadCount) && $navUnreadCount > 0)
                                <span class="badge badge-warning right">{{ $navUnreadCount }}</span>
                            @endif
                        </p>
                    </a>
                </li>

                <!-- Laporan -->
                <li class="nav-item">
                    <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-file-invoice text-teal"></i>
                        <p>Laporan & Ekspor</p>
                    </a>
                </li>

                @if (Auth::user()->hasRole(['admin', 'management']))
                    <li class="nav-header font-weight-bold text-uppercase text-secondary mt-2">PENGELOLAAN & AUDIT</li>

                    <!-- Log Aktivitas -->
                    <li class="nav-item">
                        <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-history text-muted"></i>
                            <p>Log Aktivitas</p>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->isAdmin())
                    <!-- Manajemen Pengguna -->
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users-cog"></i>
                            <p>Manajemen Pengguna</p>
                        </a>
                    </li>

                    <!-- Pengaturan -->
                    <li class="nav-item">
                        <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-sliders-h"></i>
                            <p>Pengaturan Sistem</p>
                        </a>
                    </li>
                @endif

                <li class="nav-header font-weight-bold text-uppercase text-secondary mt-2">AKUN PENGGUNA</li>

                <li class="nav-item">
                    <a href="{{ route('profile') }}" class="nav-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-circle"></i>
                        <p>Profil Saya</p>
                    </a>
                </li>

                <li class="nav-item mt-2">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-link btn btn-outline-danger text-left text-white w-100 border-0" style="background: rgba(220,53,69,0.15);">
                            <i class="nav-icon fas fa-power-off text-danger"></i>
                            <p class="font-weight-bold text-danger">Keluar (Logout)</p>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </div>
</aside>
