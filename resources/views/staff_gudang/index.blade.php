@extends('layouts/main')

@section('container')
    <!DOCTYPE html>
    <html lang="en">

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
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                </ol>
            </nav>
            <div class="page-content">
                <section class="row">
                    <div class="col-12 col-lg-12">
                        <div class="row">
                            <div class="col-6 col-lg-4 col-md-6">
                                <div class="card shadow h-md-50">
                                    <div class="card-body px-4 py-4-5">
                                        <div class="row">
                                            <div class="d-flex align-items-center justify-content-start">
                                                <div class="stats-icon purple mb-2">
                                                    <i class="iconly-boldShow"></i>
                                                </div>
                                                <div class="mx-4">
                                                    <h6 class="text-muted font-semibold">Manajement Barang</h6>
                                                    <a href="stokBarang" class="btn btn-success" style="margin-right:5px;">
                                                        <i class="bi bi-clipboard-plus-fill"></i> Stok Barang
                                                    </a>
                                                    <a href="barangKeluar" class="btn btn-danger">
                                                        <i class="bi bi-clipboard-minus-fill"></i> Barang Keluar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                                            <div id="chartdiv"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-lg-3">
                                    <div class="card shadow h-md-50">
                                        <div class="card-header">
                                            <h4>Presentase Penjualan Type</h4>
                                        </div>
                                        <div class="card-body">
                                            <div id="piechartdiv"></div>
                                        </div>
                                    </div>
                                </div>
                                {{-- <div class="col-12 col-lg-4">
                                <div class="card shadow h-95">
                                    <div class="card-header">
                                        <h4>Kotak Pesan</h4>
                                    </div>
                                    <div class="card-body mb-2 overflow-auto">
                                        <div class="overflow" style="max-height: 160px; overflow: auto;">
                                            
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="bullet bullet-vertical h-40px bg-primary"></span>
                                                <div class="flex-grow-1 mx-5">
                                                    <h3 class="text-gray-800 text-hover-primary fw-bolder fs-6">Pesan Masuk
                                                    </h3>
                                                    <span class="text-muted fw-bold d-block">T.Said Aghil Zailani melakukan
                                                        pengiriman pesan </span>
                                                </div>
                                                <span class="badge badge-secondary fs-8 fw-bolder fst-italic"
                                                    style="color: grey;">21-August-2023 16:21:32</span>
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>                             --}}                                                            
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
                    country: "{{ $item->namaBarang }}",
                    value: {{ $item->stokBarang }}
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
                    categoryField: "category",
                    alignLabels: false
                }));

                series.labels.template.setAll({
                    textType: "circular",
                    centerX: 0,
                    centerY: 0
                });

                // isi data chart
                series.data.setAll([{
                        value: {{ $rs }},
                        category: "Racking System"
                    },
                    {
                        value: {{ $fp }},
                        category: "Fire Protection"
                    },
                ]);

                // legend atau info kecil kecil dibawah chart
                var legend = chart.children.push(am5.Legend.new(root, {
                    centerX: am5.percent(50),
                    x: am5.percent(50),
                    marginTop: 15,
                    marginBottom: 15,
                }));

                legend.data.setAll(series.dataItems);

                series.appear(1000, 100);
            });
        </script>
        <!-- styling pie chart -->
    </body>

    </html>
@endsection
