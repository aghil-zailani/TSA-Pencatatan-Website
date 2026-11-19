<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tunas Siak Anugrah | {{ $judul }} </title>

    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app.css') }}" />
    <link rel="stylesheet" href="{{ url('/dist/assets/extensions/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
    <link rel="stylesheet" href="{{ url('/dist/assets/css/pages/datatables.css') }}" />
    <link rel="stylesheet" href="{{ url('dist/assets/css/main/app-dark.css') }}">
    <link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ url('dist/assets/css/shared/iconly.css') }}" />

    @stack('styles')

    <style>
    #sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        height: 100vh;
        z-index: 20;
    }

    .layout-sidebar-toggle {
        display: none;
        position: fixed;
        top: 12px;
        left: 12px;
        z-index: 30;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 6px;
        background: #8cd7ea;
        color: #243746;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    @media (max-width: 1199.98px) {
        .layout-sidebar-toggle.is-visible {
            display: flex;
        }
    }

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

    .sidebar-menu .sidebar-group {
        list-style: none;
    }

    .sidebar-menu .sidebar-group-toggle {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        border: 0;
        background: transparent;
        color: inherit;
        cursor: pointer;
        font-family: inherit;
        text-align: left;
    }

    .sidebar-menu .sidebar-group-toggle .sidebar-group-chevron {
        transition: transform 0.2s ease;
    }

    .sidebar-menu .sidebar-group-toggle[aria-expanded="true"] .sidebar-group-chevron {
        transform: rotate(180deg);
    }

    .sidebar-menu .sidebar-submenu {
        display: none;
        padding-left: 0;
    }

    .sidebar-menu .sidebar-group.is-open .sidebar-submenu {
        display: block;
    }

    .info-item {
            margin-bottom: 1rem;
        }

        .info-value {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #e9ecef;
            font-weight: 500;
            color: #495057;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }

        #status option {
            padding: 10px;
            font-size: 1rem;
        }

        .form-select-lg {
            padding: 0.75rem 1rem;
            font-size: 1.1rem;
        }

        .modal-content {
            border-radius: 1rem;
            overflow: hidden;
        }

        .modal-backdrop {
            background-color: rgba(15, 23, 42, 0.20) !important;
        }

        .modal-backdrop.show {
            opacity: 1 !important;
        }

        .modal-header {
            padding: 1.5rem;
        }

        .border-end {
            border-right: 2px solid #e9ecef !important;
        }

        /* Smooth transitions */
        .modal.fade .modal-dialog {
            transform: translate(0, -50px);
            transition: all 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: translate(0, 0);
        }

        #foto_preview {
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        #foto_preview:hover {
            transform: scale(1.05);
        }
        
        .qr-card-container {
            text-align: center;
            padding: 10px;
        }

        .qr-product-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .qr-subtitle {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 20px;
        }

        .qr-image-wrapper {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            border: 2px dashed #cbd5e0;
            display: inline-block;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .qr-image-wrapper:hover {
            transform: scale(1.02);
            border-color: #3498db;
        }

        .qr-img-display {
            width: 200px;
            height: 200px;
            object-fit: contain;
        }

        .swal2-confirm-custom {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 1rem;
            transform: scale(0.98) !important;
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
                            <li class="sidebar-title text-center">
                                Admin
                            </li>
                            <hr />

                            <li class="sidebar-group {{ request()->is('dashboard*') ? 'is-open' : '' }}">
                                <button type="button" class="sidebar-title sidebar-group-toggle" aria-expanded="{{ request()->is('dashboard*') ? 'true' : 'false' }}" aria-controls="sidebar-dashboard">
                                    <span>Dashboard</span>
                                    <i class="bi bi-chevron-down sidebar-group-chevron"></i>
                                </button>
                                <ul class="sidebar-submenu" id="sidebar-dashboard">
                                    <li class="sidebar-item {{ request()->is('dashboard*') ? 'active' : '' }}">
                                        <a href="{{ route('dashboard') }}" class='sidebar-link'>
                                            <i class="bi bi-speedometer2"></i>
                                            <span>Dashboard</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="sidebar-group {{ request()->is('staff-gudang/data-barang*', 'supervisor/monitoring-stok*') ? 'is-open' : '' }}">
                                <button type="button" class="sidebar-title sidebar-group-toggle" aria-expanded="{{ request()->is('staff-gudang/data-barang*', 'supervisor/monitoring-stok*') ? 'true' : 'false' }}" aria-controls="sidebar-data-stok">
                                    <span>Data dan Stok</span>
                                    <i class="bi bi-chevron-down sidebar-group-chevron"></i>
                                </button>
                                <ul class="sidebar-submenu" id="sidebar-data-stok">
                                    <li class="sidebar-item {{ request()->is('staff-gudang/data-barang*') ? 'active' : '' }}">
                                        <a href="{{ route('staff_gudang.data-barang') }}" class='sidebar-link'>
                                            <i class="bi bi-upc-scan"></i>
                                            <span>Data Barang dan QR</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('supervisor/monitoring-stok*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor.monitoring') }}" class='sidebar-link'>
                                            <i class="bi bi-box-seam"></i>
                                            <span>Monitoring Stok</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="sidebar-group {{ request()->is('staff-gudang/form-pengajuan*', 'staff-gudang/input-data*', 'staff-gudang/catat-barang-keluar*') ? 'is-open' : '' }}">
                                <button type="button" class="sidebar-title sidebar-group-toggle" aria-expanded="{{ request()->is('staff-gudang/form-pengajuan*', 'staff-gudang/input-data*', 'staff-gudang/catat-barang-keluar*') ? 'true' : 'false' }}" aria-controls="sidebar-input-pengajuan">
                                    <span>Input dan Pengajuan</span>
                                    <i class="bi bi-chevron-down sidebar-group-chevron"></i>
                                </button>
                                <ul class="sidebar-submenu" id="sidebar-input-pengajuan">
                                    <li class="sidebar-item {{ request()->is('staff-gudang/form-pengajuan*') ? 'active' : '' }}">
                                        <a href="{{ route('staff_gudang.form_pengajuan') }}" class='sidebar-link'>
                                            <i class="bi bi-clipboard2-plus"></i>
                                            <span>Pengajuan Pemeliharaan</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('staff-gudang/input-data*') ? 'active' : '' }}">
                                        <a href="{{ route('staff_gudang.input_data') }}" class='sidebar-link'>
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Input Data Barang</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('staff-gudang/catat-barang-keluar*') ? 'active' : '' }}">
                                        <a href="{{ route('staff_gudang.barang_keluar.create') }}" class='sidebar-link'>
                                            <i class="bi bi-box-arrow-up-right"></i>
                                            <span>Catat Barang Keluar</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="sidebar-group {{ request()->is('supervisor/validasi-barang-masuk*', 'supervisor/validasi-laporan/*', 'supervisor/validasi/barang-keluar*', 'supervisor/pemeliharaan-validasi*', 'supervisor/pemeliharaan-riwayat*') ? 'is-open' : '' }}">
                                <button type="button" class="sidebar-title sidebar-group-toggle" aria-expanded="{{ request()->is('supervisor/validasi-barang-masuk*', 'supervisor/validasi-laporan/*', 'supervisor/validasi/barang-keluar*', 'supervisor/pemeliharaan-validasi*', 'supervisor/pemeliharaan-riwayat*') ? 'true' : 'false' }}" aria-controls="sidebar-validasi-pemeliharaan">
                                    <span>Validasi dan Pemeliharaan</span>
                                    <i class="bi bi-chevron-down sidebar-group-chevron"></i>
                                </button>
                                <ul class="sidebar-submenu" id="sidebar-validasi-pemeliharaan">
                                    <li class="sidebar-item {{ request()->is('supervisor/validasi-barang-masuk*') || request()->is('supervisor/validasi-laporan/*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor.validasi.barang_masuk') }}" class='sidebar-link'>
                                            <i class="bi bi-check2-square"></i>
                                            <span>Validasi Barang Masuk</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('supervisor/validasi/barang-keluar*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor.validasi.barang_keluar') }}" class='sidebar-link'>
                                            <i class="bi bi-box-arrow-up-right"></i>
                                            <span>Validasi Barang Keluar</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('supervisor/pemeliharaan-validasi*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor.pemeliharaan.validasi') }}" class='sidebar-link'>
                                            <i class="bi bi-tools"></i>
                                            <span>Verifikasi Pemeliharaan</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('supervisor/pemeliharaan-riwayat*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor.pemeliharaan.riwayat') }}" class='sidebar-link'>
                                            <i class="bi bi-wrench-adjustable-circle"></i>
                                            <span>Riwayat Pemeliharaan</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="sidebar-group {{ request()->is('supervisor/riwayat*', 'staff-gudang/riwayat-aktivitas*', 'partner/riwayat*', 'supervisor/log-aktivitas*') ? 'is-open' : '' }}">
                                <button type="button" class="sidebar-title sidebar-group-toggle" aria-expanded="{{ request()->is('supervisor/riwayat*', 'staff-gudang/riwayat-aktivitas*', 'partner/riwayat*', 'supervisor/log-aktivitas*') ? 'true' : 'false' }}" aria-controls="sidebar-riwayat-audit">
                                    <span>Riwayat dan Audit</span>
                                    <i class="bi bi-chevron-down sidebar-group-chevron"></i>
                                </button>
                                <ul class="sidebar-submenu" id="sidebar-riwayat-audit">
                                    <li class="sidebar-item {{ request()->is('supervisor/riwayat*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor.riwayat') }}" class='sidebar-link'>
                                            <i class="bi bi-clock-history"></i>
                                            <span>Riwayat Laporan</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('staff-gudang/riwayat-aktivitas*') ? 'active' : '' }}">
                                        <a href="{{ route('staff_gudang.riwayat.aktivitas') }}" class='sidebar-link'>
                                            <i class="bi bi-journal-text"></i>
                                            <span>Riwayat Aktivitas Gudang</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('partner/riwayat*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor_umum.riwayat') }}" class='sidebar-link'>
                                            <i class="bi bi-journal-check"></i>
                                            <span>Riwayat Laporan Partner</span>
                                        </a>
                                    </li>
                                    <li class="sidebar-item {{ request()->is('supervisor/log-aktivitas*') ? 'active' : '' }}">
                                        <a href="{{ route('supervisor.log.aktivitas') }}" class='sidebar-link'>
                                            <i class="bi bi-list-task"></i>
                                            <span>Log Aktivitas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    @endauth
                </div>
                <div class="sidebar-logout">
                    <form onsubmit="return confirm('Apakah Anda Yakin Ingin Keluar?')" action="{{ route('logout') }}" id="logoutForm" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100" id="logoutButton" style="cursor: pointer;">
                            <i class="bi bi-power"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <button type="button" class="burger-btn layout-sidebar-toggle" aria-label="Buka sidebar" title="Buka menu">
            <i class="bi bi-list fs-3"></i>
        </button>
            </div>
        </div>
    </div>

    <!-- js me -->
    <script src="{{ url('dist/assets/js/app.js') }}"></script>
    <script src="{{ url('dist/assets/js/bootstrap.js') }}"></script>
    <script src="{{ url('dist/assets/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ url('dist/assets/extensions/jquery/jquery.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="{{ url('dist/assets/extensions/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ url('dist/assets/js/pages/datatables.js') }}"></script>
    <!-- js me -->

    @if (request()->routeIs('dashboard', 'supervisor.dashboard', 'staff_gudang.dashboard'))
        <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
        <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    @endif

    <script src="{{ url('dist/assets/extensions/sweetalert2/sweetalert2.all.min.js') }}"></script>
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
    <script>
        document.addEventListener('click', function(event) {
            const toggle = event.target.closest('.sidebar-group-toggle');
            if (!toggle) return;

            const group = toggle.closest('.sidebar-group');
            if (!group) return;

            const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!isExpanded));
            group.classList.toggle('is-open', !isExpanded);
        });
    </script>
    <script>
        document.addEventListener('click', function(event) {
            const trigger = event.target.closest('.burger-btn, .sidebar-hide');
            if (!trigger) return;

            event.preventDefault();
            event.stopImmediatePropagation();

            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;

            if (trigger.classList.contains('sidebar-hide')) {
                sidebar.classList.remove('active');
            } else {
                sidebar.classList.toggle('active');
            }

            const layoutToggle = document.querySelector('.layout-sidebar-toggle');
            if (layoutToggle) {
                layoutToggle.classList.toggle('is-visible', !sidebar.classList.contains('active'));
            }
        }, true);
    </script>
    @stack('scripts')
@yield('scripts')
</body>
@yield('container');