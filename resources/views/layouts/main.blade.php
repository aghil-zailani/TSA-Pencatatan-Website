<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tunas Siak Anugrah | {{ $judul }} </title>

    <!-- css me -->
    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app.css') }}" />
    <link rel="stylesheet"
        href="{{ url('/dist/assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ url('/dist/assets/css/pages/datatables.css') }}" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app.css') }}">
    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app-dark.css') }}">
    <link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/png">
    <link href="{{ url('/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <!-- css me -->

    <link rel="shortcut icon" href="logo/tsa.png" type="image/png" />
    <link rel="stylesheet" href="{{ url('dist/assets/css/shared/iconly.css') }}" />
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
                    <ul class="menu">
                        <li class="sidebar-title text-center">Supervisor</li>
                        <hr />
                        <li class="sidebar-item">
                            <a href="home" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="input" class="sidebar-link">
                                <i class="bi bi-save-fill"></i>
                                <span>Monitoring Stok Barang</span>
                            </a>
                        </li>
                        <li class="sidebar-item has-sub">
                            <a href="" class="sidebar-link">
                                <i class="bi bi-menu-button-wide-fill"></i>
                                <span>Validasi</span>
                            </a>
                            <ul class="submenu">
                                <li class="submenu-item">
                                    <a href="/stokBarang">Barang Masuk</a>
                                </li>
                                <li class="submenu-item">
                                    <a href="/barangKeluar">Barang Keluar</a>
                                </li>
                            </ul>
                        </li>
                        <li class="sidebar-item">
                            <a href="home" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Laporan Barang</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="home" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Pemeliharaan</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="home" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Riwayat</span>
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="home" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Log Aktivitas</span>
                            </a>
                        </li>
                        <br>
                        <form onsubmit="return confirm('Apakah Anda Yakin Ingin Keluar?')" action="{{ route('logout') }}" method="POST" class="mb-3 px-5 fixed-bottom float-start"
                            style="margin-left: 10px">
                            @csrf
                            <button type="submit" class="btn icon btn-danger px-5" >
                                <i class="bi-power bold"></i> Logout
                            </button>
                        </form>
                    </ul>
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

    {{-- styling stock chart --}}
</body>
@yield('container');
