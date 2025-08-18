@extends('layouts/main')

@section('container')
    <!DOCTYPE html>
    <html lang="en">
        <style>
            .list-item-custom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.8rem 0.5rem;
                border-bottom: 1px solid #f0f0f0; /* Garis pemisah tipis */
            }
            .list-item-custom:last-child {
                border-bottom: none; /* Hilangkan border untuk item terakhir */
            }
            .list-item-custom .icon-link {
                font-size: 1.2rem;
                color: #6c757d; /* Warna ikon abu-abu */
                text-decoration: none;
                transition: color 0.3s;
            }
            .list-item-custom .icon-link:hover {
                color: #435ebe; /* Warna ikon saat di-hover */
            }
            .time-badge {
                background-color: #eef2f7; /* Warna abu-abu muda */
                color: #474747; /* Warna teks gelap */
                padding: 0.25rem 0.6rem;
                border-radius: 0.5rem; /* Sedikit lebih rounded */
                font-size: 0.8em;
                font-weight: 500;
            }
            .card-scrollable .card-body {
                /* Tentukan tinggi maksimal untuk area konten */
                max-height: 250px; /* Anda bisa menyesuaikan tinggi ini sesuai kebutuhan */

                /* Tambahkan scrollbar vertikal hanya jika konten melebihi max-height */
                overflow-y: auto;
            }

            /* Styling tambahan untuk scrollbar agar lebih modern (opsional) */
            .card-scrollable .card-body::-webkit-scrollbar {
                width: 6px;
            }
            .card-scrollable .card-body::-webkit-scrollbar-thumb {
                background-color: #c5c5c5;
                border-radius: 10px;
            }
            .card-scrollable .card-body::-webkit-scrollbar-track {
                background-color: #f1f1f1;
            }

            .modal-backdrop.show {
                opacity: 0.5 !important; /* Membuat overlay lebih gelap */
            }

            .modal-content {
                border-radius: 1rem;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            }

            .modal-header {
                background-color: #f8d7da; /* Warna latar belakang header merah muda */
                color: #721c24; /* Warna teks merah gelap */
                border-bottom: none;
                border-top-left-radius: 1rem;
                border-top-right-radius: 1rem;
                padding: 1.5rem;
                font-family: 'Poppins', sans-serif; /* Menggunakan Poppins jika tersedia */
            }

            .modal-title {
                font-weight: bold;
                display: flex; /* Menggunakan flexbox untuk ikon dan teks */
                align-items: center; /* Menyelaraskan ikon dan teks secara vertikal */
            }

            .modal-title i {
                font-size: 1.5rem; /* Ukuran ikon peringatan lebih besar */
                margin-right: 0.75rem; /* Jarak antara ikon dan teks */
                color: #dc3545; /* Warna ikon peringatan yang jelas */
            }

            .modal-header .btn-close {
                filter: invert(30%) sepia(100%) saturate(7000%) hue-rotate(330deg) brightness(90%) contrast(80%); /* Mengubah warna ikon silang menjadi merah gelap */
            }

            .modal-body {
                padding: 1.5rem;
                font-family: 'Poppins', sans-serif;
            }

            .list-group-item-stock {
                font-family: 'Poppins', sans-serif;
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.75rem 1.25rem;
                border: none; /* Hilangkan border item default */
                border-bottom: 1px solid #eee; /* Garis pemisah antar item */
                font-size: 0.95rem; /* Ukuran font sedikit lebih besar */
                color: #343a40; /* Warna teks yang lebih gelap */
            }
            .list-group-item-stock:last-child {
                border-bottom: none;
            }

            /* Badge Stok yang Lebih Estetik */
            .list-group-item-stock .badge-stock {
                background-color: #ffc107; /* Warna kuning Bootstrap default */
                color: #212529; /* Teks hitam atau gelap */
                padding: 0.4em 0.8em; /* Padding badge lebih baik */
                border-radius: 0.5rem; /* Rounded corner */
                font-weight: bold;
                min-width: 60px; /* Lebar minimum agar konsisten */
                text-align: center; /* Teks di tengah badge */
                box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); /* Shadow tipis */
            }

            .modal-footer {
                border-top: none;
                padding: 1rem 1.5rem 1.5rem;
                color:white;
            }
            .btn-primary-stock-modal {
                background-color: #435ebe; /* Warna biru konsisten dengan tema Mazer */
                border-color: #435ebe;
                font-family: 'Poppins', sans-serif;
                border-radius: 0.5rem;
                padding: 0.6rem 1.2rem;
                color: white;
            }
            .btn-primary-stock-modal:hover {
                background-color: #394f99;
                border-color: #394f99;
            }
        </style>

    <body>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>

            <div class="page-heading">
                <h3>Dashboard</h3>
            </div>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-12">
                        <div class="row">
                            <div class="col-4 col-lg-3 col-md-3">
                            <div class="card">
                                <div class="card-body px-4 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                            <div class="stats-icon purple mb-2">
                                                <i class="iconly-boldArrow---Down-Square"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                            <h6 class="text-muted font-semibold">Total Keseluruhan Barang</h6>
                                            <h6 class="font-extrabold mb-0">{{ $totalKeseluruhanBarang ?? 0 }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-4 col-lg-3 col-md-3">
                            <div class="card">
                                <div class="card-body px-4 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                            <div class="stats-icon blue mb-2">
                                                <i class="iconly-boldArrow---Up-Square"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                            <h6 class="text-muted font-semibold">Barang Masuk</h6>
                                            <h6 class="font-extrabold mb-0">{{ $barangMasukBulanIni ?? 0 }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>  
                        <div class="col-4 col-lg-3 col-md-3">
                            <div class="card">
                                <div class="card-body px-4 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                            <div class="stats-icon blue mb-2">
                                                <i class="iconly-boldArrow---Up-Square"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                            <h6 class="text-muted font-semibold">Barang Keluar</h6>
                                            <h6 class="font-extrabold mb-0">{{ $barangKeluarBulanIni ?? 0 }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3 col-md-6">
                            <a href="{{ route('supervisor.monitoring') }}">
                            <div class="card">
                                <div class="card-body px-4 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                            <div class="stats-icon red mb-2">
                                                <i class="iconly-boldDanger"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                            <h6 class="text-muted font-semibold">Stok Minimum</h6>
                                            <h6 class="font-extrabold mb-0">{{ $lowStockItems->count() }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </a>
                        </div>                     
                            <div class="row">
                                <div class="col-12 col-lg-9">
                                    <div class="card shadow h-md-50">
                                        <div class="card-header">
                                            <h4 class="card-title align-items-start flex-column">
                                                <span class="card-label fw-border">Stok Barang</span>
                                            </h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="chartdiv" style="width: 100%; height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <div class="card shadow h-md-50">
                                        <div class="card-header">
                                            <h4>Presentase Stok Tipe Barang</h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="piechartdiv" style="width: 100%; height: 350px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 col-lg-7">
                                    <div class="card card-scrollable">
                                        <div class="card-header">
                                            <h4>Riwayat Laporan</h4>
                                        </div>
                                        <div class="card-body">
                                            @forelse ($laporanGabungan as $laporan)
                                                <div class="list-item-custom">
                                                    <div>
                                                        @if($laporan->nama_laporan)
                                                        <strong>{{ $laporan->nama_laporan }}</strong>
                                                        @elseif($laporan->nama_laporan != 'Laporan Barang Masuk') 
                                                        <strong>Laporan Barang Keluar</strong>
                                                        @endif
                                                        <br>
                                                    </div>
                                                    <span class="time-badge">{{ $laporan->created_at }}</span>
                                                    <span class="{{ $laporan->badge_class }}">{{ $laporan->display_status }}</span>
                                                </div>
                                            @empty
                                                <p class="text-center text-muted">Belum ada riwayat laporan validasi.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-5">
                                <div class="card card-scrollable">
                                    <div class="card-header">
                                        <h4>Riwayat Login</h4>
                                    </div>
                                    <div class="card-body">
                                        @forelse ($riwayatLogin as $history)
                                            <div class="list-item-custom">
                                                {{-- Bagian teks di sebelah kiri --}}
                                                <div class="text-wrapper">
                                                    <span class="fw-bold d-block">
                                                        Login
                                                    </span>
                                                    <span class="text-muted small">
                                                        {{ $history->user->username ?? '' }} Melakukan Login Pada
                                                    </span>
                                                </div>

                                                {{-- Bagian tanggal di sebelah kanan --}}
                                                <span class="time-badge">
                                                    {{-- Format tanggal dan waktu sesuai gambar --}}
                                                    {{ $history->login_at->format('d M Y H:i:s') }}
                                                </span>
                                            </div>
                                        @empty
                                            <p class="text-center text-muted">Belum ada riwayat login.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>                                                                                  
                        </div>
                    </div>
                </section>
            </div>

            <footer>
                <div class="footer clearfix mb-0 text-muted text-center">
                    <p>Tunas Siak Anugrah &copy; | 2023</p>
                </div>
            </footer>
        </div>

        @if(session('lowStockItems') && session('lowStockItems')->count())
            <div class="modal fade" id="stockNotificationModal" tabindex="-1" aria-labelledby="stockNotificationModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="stockNotificationModalLabel"><i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Notifikasi Stok Minimum!</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 mb-4">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill text-warning me-2"></i>
                                    <span>Barang-barang berikut memiliki stok di bawah batas minimum (10 unit)</span>
                                </div>
                            </div>

                            <!-- Items List -->
                            <div class="row g-3">
                                @foreach(session('lowStockItems') as $item)
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="card-title mb-2 fw-semibold">
                                                        {{ $item->nama_barang ?? 'Nama tidak tersedia' }}
                                                    </h6>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-box-seam text-muted me-2"></i>
                                                        <span class="text-muted small">Stok tersisa:</span>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    @php
                                                        $stockLevel = $item->stok;
                                                        $badgeClass = $stockLevel <= 2 ? 'bg-danger' : ($stockLevel <= 5 ? 'bg-warning text-dark' : 'bg-info');
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }} px-3 py-2 rounded-pill fs-6">
                                                        {{ $item->stok }} unit
                                                    </span>
                                                    @if($stockLevel <= 2)
                                                        <div class="text-danger small mt-1">
                                                            <i class="bi bi-exclamation-circle-fill me-1"></i>Kritis
                                                        </div>
                                                    @elseif($stockLevel <= 5)
                                                        <div class="text-warning small mt-1">
                                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Rendah
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <!-- Action Suggestion -->
                            <div class="mt-4 p-3 bg-light rounded-3">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bi bi-lightbulb-fill me-2"></i>
                                    <small>
                                        <strong>Saran:</strong> Data lebih lengkapnya dapat dilihat pada 
                                        <a href="{{ route('staff_gudang.monitoring') }}">monitoring stok barang</a>.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- line chart css -->
        <style>
            #chartdiv {
                width: 100%;
                height: 500px;
            }
        </style>
        <!-- line chart css -->

        <!-- pie chart  css -->
        <style>
            #piechartdiv {
                width: 100%;
                height: 500px;
            }
        </style>
        <!-- pie chart  css -->

        <style>
            #chartstock {
                width: 100%;
                height: 500px;
            }
        </style>

        <!-- styling bar chart -->
        <script>
            am5.ready(function() {

                // Create root element
                // https://www.amcharts.com/docs/v5/getting-started/#Root_element
                var root = am5.Root.new("chartdiv");


                // Set themes
                // https://www.amcharts.com/docs/v5/concepts/themes/
                root.setThemes([
                    am5themes_Animated.new(root)
                ]);


                // Create chart
                // https://www.amcharts.com/docs/v5/charts/xy-chart/
                var chart = root.container.children.push(am5xy.XYChart.new(root, {
                    panX: true,
                    panY: true,
                    wheelX: "panX",
                    wheelY: "zoomX",
                    pinchZoomX: true
                }));

                // Add cursor
                // https://www.amcharts.com/docs/v5/charts/xy-chart/cursor/
                var cursor = chart.set("cursor", am5xy.XYCursor.new(root, {}));
                cursor.lineY.set("visible", false);


                // Create axes
                // https://www.amcharts.com/docs/v5/charts/xy-chart/axes/
                var xRenderer = am5xy.AxisRendererX.new(root, {
                    minGridDistance: 30
                });
                xRenderer.labels.template.setAll({
                    rotation: -90,
                    centerY: am5.p50,
                    centerX: am5.p100,
                    paddingRight: 15
                });

                xRenderer.grid.template.setAll({
                    location: 1
                })

                var xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(root, {
                    maxDeviation: 0.3,
                    categoryField: "country",
                    renderer: xRenderer,
                    tooltip: am5.Tooltip.new(root, {})
                }));

                var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(root, {
                    maxDeviation: 0.3,
                    renderer: am5xy.AxisRendererY.new(root, {
                        strokeOpacity: 0.1
                    })
                }));


                // Create series
                // https://www.amcharts.com/docs/v5/charts/xy-chart/series/
                var series = chart.series.push(am5xy.ColumnSeries.new(root, {
                    name: "Series 1",
                    xAxis: xAxis,
                    yAxis: yAxis,
                    valueYField: "value",
                    sequencedInterpolation: true,
                    categoryXField: "country",
                    tooltip: am5.Tooltip.new(root, {
                        labelText: "{valueY}"
                    })
                }));

                series.columns.template.setAll({
                    cornerRadiusTL: 5,
                    cornerRadiusTR: 5,
                    strokeOpacity: 0
                });
                series.columns.template.adapters.add("fill", function(fill, target) {
                    return chart.get("colors").getIndex(series.columns.indexOf(target));
                });

                series.columns.template.adapters.add("stroke", function(stroke, target) {
                    return chart.get("colors").getIndex(series.columns.indexOf(target));
                });


                // Set data
                var data = [];
                @foreach($chart as $item)
                data.push({
                    country: "{{ $item['country'] }}",
                    value: {{ $item['value'] }}
                });
                @endforeach

                xAxis.data.setAll(data);
                series.data.setAll(data);


                // Make stuff animate on load
                // https://www.amcharts.com/docs/v5/concepts/animations/
                series.appear(1000);
                chart.appear(1000, 100);

            }); // end am5.ready()
        </script>
        <!-- styling bar chart -->

        <!-- styling pie chart -->
        <script>
            am5.ready(function() {

                var root = am5.Root.new("piechartdiv");

                root.setThemes([
                    am5themes_Animated.new(root)
                ]);

                var chart = root.container.children.push(am5percent.PieChart.new(root, {
                    layout: root.verticalLayout,
                    innerRadius: am5.percent(50)
                }));

                var series = chart.series.push(am5percent.PieSeries.new(root, {
                    valueField: "value",
                    categoryField: "tipe_barang",
                    alignLabels: false
                }));

                // [UBAH] Atur format teks untuk label di potongan chart
                series.labels.template.setAll({
                    textType: "circular",
                    centerX: 0,
                    centerY: 0,
                    // Tampilkan kategori dan jumlah aktualnya
                    text: "{category}: {value}" 
                });

                // [BARU] Atur format teks untuk tooltip (saat di-hover)
                series.set("tooltip", am5.Tooltip.new(root, {
                    labelText: "{category}: {value} unit"
                }));


                // legend atau info kecil kecil dibawah chart
                var legend = chart.children.push(am5.Legend.new(root, {
                    centerX: am5.percent(50),
                    x: am5.percent(50),
                    marginTop: 15,
                    marginBottom: 15,
                }));
                
                // [BARU] Atur format teks untuk nilai di legenda
                legend.valueLabels.template.setAll({
                    // Tampilkan hanya jumlah aktualnya saja
                    text: "{value}"
                });

                // isi data chart (TETAP SAMA)
                series.data.setAll([{
                        value: {{ $rs }},
                        tipe_barang: "Sparepart" // 'category' diganti 'tipe_barang' agar konsisten
                    },
                    {
                        value: {{ $fp }},
                        tipe_barang: "Barang Jadi" // 'category' diganti 'tipe_barang'
                    },
                ]);


                legend.data.setAll(series.dataItems);

                series.appear(1000, 100);
            });
        </script>
        <!-- styling pie chart -->

        <!-- Script untuk Notifikasi Stok Minimum -->
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Cek apakah elemen modal ada di dalam HTML
                const stockModalElement = document.getElementById('stockNotificationModal');

                // Jika elemennya ADA (artinya Blade merendernya karena session ada)
                if (stockModalElement) {
                    // Buat instance modal dan langsung tampilkan
                    var stockModal = new bootstrap.Modal(stockModalElement);
                    stockModal.show();
                }
            });
        </script>
        <!-- Akhir Script Notifikasi Stok Minimum -->
    </body>

    </html>
@endsection
