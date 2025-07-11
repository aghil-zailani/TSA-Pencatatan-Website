<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tunas Siak Anugrah | {{ $judul }} </title>

    <!-- css me -->
    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app.css') }}" />
    <link rel="stylesheet" href="{{ url('/dist/assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ url('/dist/assets/css/pages/datatables.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app.css') }}">
    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app-dark.css') }}">
    <link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/png">
    <link href="{{ url('/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <!-- css me -->

    <link rel="shortcut icon" href="logo/tsa.png" type="image/png" />
    <link rel="stylesheet" href="{{ url('dist/assets/css/shared/iconly.css') }}" />

    <style>
    .sidebar-wrapper {
        display: flex;
        flex-direction: column;
        height: 100vh;
        justify-content: space-between;
    }

    .sidebar-wrapper .sidebar-menu {
        overflow-y: auto;
        flex-grow: 1; 
    }

    .sidebar-wrapper .sidebar-logout {
        flex-shrink: 0;
        padding: 1rem 1.5rem;
        background-color: inherit;
    }
</style>
    
</head>

<body>
    <div id="app">
        <div id="sidebar" class="active" style="background-color: #8cd7ea;">
            <div class="sidebar-wrapper active">
                <div class="sidebar-header position-relative">
                    <div class="sidebar-toggler x">
                        <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="aside-logo flex-column-auto px-9 mb-9 mb-lg-20 mx-auto">
                            <a href="/">
                                <img src="{{ url('logo/tsa1.png') }}" alt="Logo" style="width: 190px; height: auto" />
                            </a>
                        </div>
                    </div>
                </div>

                <div class="sidebar-menu">
                    @auth
                        <ul class="menu">
                            {{-- Judul Sidebar Dinamis Berdasarkan Role --}}
                            <li class="sidebar-title text-center">
                                @if(Auth::user()->role == 'supervisor')
                                    Supervisor
                                @elseif(Auth::user()->role == 'staff_gudang')
                                    Staff Gudang
                                @endif
                            </li>
                            <hr />

                            @if(Auth::user()->role == 'supervisor')
                                <li class="sidebar-item {{ request()->is('supervisor/dashboard*') ? 'active' : '' }}">
                                    <a href="{{ route('supervisor.dashboard') }}" class='sidebar-link'>
                                        <i class="bi bi-speedometer2"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ Request::is('supervisor/master-data*') ? 'active' : '' }}">
                                    <a href="{{ route('supervisor.master.data') }}" class='sidebar-link'>
                                        <i class="bi bi-hdd-rack"></i> {{-- Icon yang relevan --}}
                                        <span>Master Data</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('supervisor/monitoring-stok*') ? 'active' : '' }}">
                                    <a href="{{ route('supervisor.monitoring') }}" class='sidebar-link'>
                                        <i class="bi bi-box-seam"></i>
                                        <span>Monitoring Stok Barang</span>
                                    </a>
                                </li>
                                <li class="sidebar-item has-sub {{ request()->is('supervisor/validasi*') ? 'active' : '' }}">
                                    <a href="#" class='sidebar-link'>
                                        <i class="bi bi-check2-square"></i>
                                        <span>Validasi</span>
                                    </a>
                                    <ul class="submenu {{ request()->is('supervisor/validasi*') ? 'active' : '' }}">
                                        <li class="submenu-item ">
                                            <a href="{{ route('supervisor.validasi.barang_masuk') }}">Barang Masuk</a>
                                        </li>
                                        <li class="submenu-item ">
                                            <a href="{{ route('supervisor.validasi.barang_keluar') }}">Barang Keluar</a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="sidebar-item {{ request()->is('supervisor/pemeliharaan*') ? 'active' : '' }}">
                                    <a href="{{ route('supervisor.pemeliharaan') }}" class='sidebar-link'>
                                        <i class="bi bi-tools"></i>
                                        <span>Pemeliharaan</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('supervisor/riwayat*') ? 'active' : '' }}">
                                    <a href="{{ route('supervisor.riwayat') }}" class='sidebar-link'>
                                        <i class="bi bi-clock-history"></i>
                                        <span>Riwayat Laporan</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('supervisor/log-aktivitas*') ? 'active' : '' }}">
                                    <a href="{{ route('supervisor.log.aktivitas') }}" class='sidebar-link'>
                                        <i class="bi bi-list-task"></i>
                                        <span>Log Aktivitas</span>
                                    </a>
                                </li>
                                
                            @elseif(Auth::user()->role == 'staff_gudang')
                                <li class="sidebar-item {{ request()->is('staff-gudang/dashboard*') ? 'active' : '' }}">
                                    <a href="{{ route('staff_gudang.dashboard') }}" class='sidebar-link'>
                                        <i class="bi bi-speedometer2"></i>
                                        <span>Dashboard</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('staff-gudang/data-barang*') ? 'active' : '' }}">
                                    <a href="{{ route('staff_gudang.data-barang') }}" class='sidebar-link'>
                                        <i class="bi bi-list-task"></i>
                                        <span>Data Barang</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('staff-gudang/monitoring*') ? 'active' : '' }}">
                                    <a href="{{ route('staff_gudang.monitoring') }}" class='sidebar-link'>
                                        <i class="bi bi-box-seam"></i>
                                        <span>Monitoring Stok Barang</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('staff-gudang/buat-laporan*') ? 'active' : '' }}">
                                    <a href="{{ route('staff_gudang.buat_laporan') }}" class='sidebar-link'>
                                        <i class="bi bi-box-arrow-in-down"></i>
                                        <span>Buat Laporan</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('staff-gudang/form-pengajuan*') ? 'active' : '' }}">
                                    <a href="{{ route('staff_gudang.form_pengajuan') }}" class='sidebar-link'>
                                        <i class="bi bi-box-arrow-up"></i>
                                        <span>Form Pengajuan</span>
                                    </a>
                                </li>
                                <li class="sidebar-item {{ request()->is('staff-gudang/riwayat-aktivitas*') ? 'active' : '' }}">
                                    <a href="{{ route('staff_gudang.riwayat.aktivitas') }}" class='sidebar-link'>
                                        <i class="bi bi-journal-text"></i>
                                        <span>Riwayat</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @endauth
                </div>
                <div class="sidebar-logout">
                    <form onsubmit="return confirm('Apakah Anda Yakin Ingin Keluar?')" action="{{ route('logout') }}" id="logoutForm" action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        {{-- Tambahkan id pada button --}}
                        <button type="submit" class="btn btn-danger w-100" id="logoutButton" style="cursor: pointer;">
                            <i class="bi bi-power"></i> 
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
            </div>
        </div>
    </div>
    <script src="{{ url('js/script.js') }}"></script>

    <!-- js me -->
    <script src="{{ url('dist/assets/js/app.js') }}"></script>
    <script src="{{ url('dist/assets/js/bootstrap.js') }}"></script>
    <script src="{{ url('dist/assets/js/mazer.js') }}"></script>
    <script src="{{ url('dist/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <!-- js me -->

    <!-- datatable -->
    <script src="{{ url('dist/assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.datatables.net/v/bs5/dt-1.12.1/datatables.min.js"></script>
    <script src="{{ url('/dist/assets/js/pages/datatables.js') }}"></script>
    <script src="{{ url('/js/sb-admin-2.min.js') }}"></script>
    <script src="{{ url('/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ url('/js/demo/datatables-demo.js') }}"></script>
    <!-- datatable -->

    <!-- js bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- js bootstrap -->

    <!-- chart resource -->
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>    
    <!-- chart resource -->

    {{-- sweetalert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- sweetalert --}}

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('logoutButton').addEventListener('click', function(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: "Apakah Anda yakin ingin keluar dari sistem?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutForm').submit();
                }
            });
        });
    </script>
@yield('scripts')
</body>
@yield('container');
