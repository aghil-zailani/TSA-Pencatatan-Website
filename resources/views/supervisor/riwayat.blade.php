@extends('layouts/main')

@section('container')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>{{ $judul }}</title>
  <style>
    body { font-family: 'Poppins', sans-serif; color: #333; }
    h3, h4 { font-family: 'Poppins', sans-serif; font-weight: 700; color: #444; }
    .card { border-radius: 0.75rem; box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05); }
    .card-header { background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; padding: 1.5rem; }
    .card-title { font-weight: 600; color: #343a40; }
    .table { --bs-table-bg: #fff; }
    .table thead th { font-family: 'Poppins', sans-serif; font-weight: 600; background-color: #e9ecef; color: #495057; text-align: center; }
    .table tbody td { font-family: 'Poppins', sans-serif; color: #495057; vertical-align: middle; }
  </style>
</head>

<body>
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
          <li class="breadcrumb-item"><a href="/home">Dashboard</a></li>
          <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
        </ol>
      </nav>

      <div class="page-content">
        <section class="section">
          <div class="card shadow h-md-50">
            <div class="card-header">
              <h4 class="card-title">Riwayat</h4>
            </div>
            <div class="card-body">
              <form method="GET" class="row g-3 mb-3">
                <div class="col-md-2">
                  <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                  <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                  <button class="btn btn-success btn-sm">Filter</button>
                  <a href="{{ route('supervisor.riwayat') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
              </form>

              <div class="table-responsive">
                <table id="dataTable" class="table table-striped table-bordered table-hover" width="100%">
                  <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama Laporan</th>
                        <th>Jumlah Item</th>
                        <th>Status</th>
                        <th>Catatan</th>
                        <th>Tanggal</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($riwayatGabung as $index => $item)
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
                        <td>
                            @if ($item->status === 'diterima')
                            <span class="badge bg-success">Diterima</span>
                            @elseif ($item->status === 'ditolak')
                            <span class="badge bg-danger">Ditolak</span>
                            @else
                            <span class="badge bg-secondary">{{ $item->status }}</span>
                            @endif
                        </td>
                        <td>{{ $item->catatan_penolakan ?? '-' }}</td>
                        <td>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                        <td colspan="6" class="text-center text-muted">Tidak ada riwayat laporan.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      $('#dataTable').DataTable({
        paging: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        pageLength: 10,
        ordering: true,
        searching: true,
        info: true,
        responsive: true
      });
    });
  </script>
</body>
</html>
@endsection
