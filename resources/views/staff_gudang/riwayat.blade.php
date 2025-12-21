@extends('layouts/main')

@section('container')
<link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/x-icon">
<link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/png">
<div id="app">
    <div id="main">
        <header class="mb-3">
            <a href="#" class="burger-btn d-block d-xl-none">
                <i class="bi bi-justify fs-3"></i>
            </a>
        </header>

        <div class="page-heading">
            <h3>{{ $judul }}</h3>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
            </ol>
        </nav>
        <div class="page-content">
            <section class="section">
                <div class="card shadow">
                    <div class="card-header">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link active" id="aktivitas-tab" data-bs-toggle="pill" href="#aktivitas">Riwayat Aktivitas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="laporan-tab" data-bs-toggle="pill" href="#laporan">Riwayat Laporan</a>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body tab-content">
                        <!-- TAB 1 -->
                         <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-2">
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                            <button class="btn btn-success btn-sm">Filter</button>
                            <a href="{{ route('supervisor.log.aktivitas') }}" class="btn btn-secondary btn-sm">Reset</a>
                            </div>
                        </form>
                        <div class="tab-pane fade show active" id="aktivitas">
                            <div class="table-responsive">
                                <table id="aktivitasTable" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                        <th>Nama</th>
                                        <th>Halaman</th>
                                        <th>Fitur</th>
                                        <th>Aksi</th>
                                        <th>Deskripsi</th>
                                        <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($logs as $log)
                                        <tr>
                                        <td>{{ $log->user->username }}</td>
                                        <td>{{ $log->page_accessed }}</td>
                                        <td>{{ $log->feature_used }}</td>
                                        <td>{{ $log->action }}</td>
                                        <td>{{ $log->description }}</td>
                                        <td>{{ $log->created_at }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- TAB 2 -->
                        <div class="tab-pane fade" id="laporan">
                            <div class="table-responsive">
                                <table id="laporanTable" class="table table-striped" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Laporan</th>
                                            <th>Jumlah Barang</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                            <th>Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($riwayatGabung as $index => $item)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>
                                                    @if ($item instanceof \App\Models\Transaksi)
                                                    Laporan Barang Keluar
                                                    @elseif ($item instanceof \App\Models\PengajuanBarang)
                                                    Laporan Barang Masuk
                                                    @endif
                                                </td>
                                                <td>{{ $item->jumlah_barang ?? '-' }}</td>
                                                <td>{{ $item->created_at }}</td>
                                                <td>
                                                    @if ($item->status == 'diterima')
                                                        <span class="badge bg-success">Diterima</span>
                                                    @else
                                                        <span class="badge bg-danger">Ditolak</span>
                                                    @endif
                                                </td>
                                                <td>{{ $item->catatan_penolakan ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                               
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@section('scripts')
<script>
  $(document).ready(function() {
    $('#aktivitasTable').DataTable({
        paging: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        pageLength: 10,
        ordering: true,
        searching: true,
        info: true,
        responsive: true
      });
    var laporanTable = $('#laporanTable').DataTable({
        paging: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        pageLength: 10,
        ordering: true,
        searching: true,
        info: true,
        responsive: true,
        order: [[ 2, 'desc' ]],

        columnDefs: [ {
            "searchable": false,
            "orderable": false,
            "targets": 0
        } ]
      });

    laporanTable.on('draw.dt', function () {
        var pageInfo = laporanTable.page.info();
        var start = pageInfo.start;
        laporanTable.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
            this.data(start + 1);
            start++;
        });
    }).draw();
  });
</script>
@endsection



