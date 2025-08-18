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
          <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
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
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse ($riwayatGabung as $index => $item)
                        <tr>
                        <td></td>
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
                        <td class="text-center">
                          @php
                              $detailUrl = '';
                              $modalTitle = '';
                              if ($item instanceof \App\Models\Transaksi) {
                                  $reportId = $item->id_transaksi;
                                  $modalTitle = 'Detail Laporan Keluar: ' . $reportId;
                                  $detailUrl = route('supervisor.riwayat.detail_keluar', $reportId);
                              } elseif ($item instanceof \App\Models\PengajuanBarang) {
                                  $reportId = $item->report_id;
                                  $modalTitle = 'Detail Laporan Masuk: ' . $reportId;
                                  $detailUrl = route('supervisor.riwayat.detail_masuk', $reportId);
                              }
                          @endphp
                          <button type="button" class="btn btn-info btn-sm view-detail-btn" 
                                  data-bs-toggle="modal" 
                                  data-bs-target="#detailLaporanModal"
                                  data-url="{{ $detailUrl }}"
                                  data-title="{{ $modalTitle }}">
                              <i class="bi bi-eye-fill"></i> Detail
                          </button>
                        </td>
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

  <div class="modal fade" id="detailLaporanModal" tabindex="-1" aria-labelledby="detailLaporanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailLaporanModalLabel">Detail Laporan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Indikator Loading --}}
                <div class="text-center" id="modalLoading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                {{-- Konten Detail --}}
                <div id="modalContent" class="d-none">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Kategori</th>
                                <th>Catatan Penolakan</th>
                                {{-- Anda bisa menambahkan header lain di sini jika perlu --}}
                            </tr>
                        </thead>
                        <tbody id="detailLaporanTbody">
                            <!-- Konten detail akan dimuat di sini oleh JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
  </div>

  <script>
    $(document).ready(function() {
      var table = $('#dataTable').DataTable({
        paging: true,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        pageLength: 10,
        ordering: true,
        searching: true,
        info: true,
        responsive: true,
        order: [[ 5, 'desc' ]], 
        columnDefs: [ {
            "searchable": false,
            "orderable": false,
            "targets": [0, 6]
        } ]
      });

      table.on('draw.dt', function () {
          var pageInfo = table.page.info();
          var start = pageInfo.start;
          table.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
              this.data(start + 1);
              start++;
          });
      }).draw();

      // ===== TAMBAHAN: Script untuk Modal Detail Laporan =====
      $('.view-detail-btn').on('click', function() {
          // Ambil data langsung dari tombol
          var url = $(this).data('url');
          var title = $(this).data('title');
          
          var modalTitle = $('#detailLaporanModalLabel');
          var modalLoading = $('#modalLoading');
          var modalContent = $('#modalContent');
          var modalTbody = $('#detailLaporanTbody');
          
          // Reset tampilan modal
          modalTbody.empty();
          modalContent.addClass('d-none');
          modalLoading.removeClass('d-none');
          modalTitle.text(title); // Set judul modal

          // Panggil data detail via AJAX menggunakan URL yang sudah jadi
          $.ajax({
              url: url,
              type: 'GET',
              success: function(response) {
                  if (response.success && response.data.length > 0) {
                      var content = '';
                      $.each(response.data, function(index, item) {
                          content += '<tr>';
                          content += '<td>' + (index + 1) + '</td>';
                          content += '<td>' + (item.nama_barang || '-') + '</td>';
                          content += '<td>' + (item.jumlah_barang || '-') + '</td>';
                          content += '<td>' + (item.tipe_barang_kategori || '-') + '</td>';
                          content += '<td>' + (item.catatan_penolakan || '-') + '</td>';
                          content += '</tr>';
                      });
                      modalTbody.html(content);
                  } else {
                      modalTbody.html('<tr><td colspan="3" class="text-center">Data detail tidak ditemukan.</td></tr>');
                  }
                  
                  modalLoading.addClass('d-none');
                  modalContent.removeClass('d-none');
              },
              error: function() {
                  modalTbody.html('<tr><td colspan="3" class="text-center">Gagal memuat data. Silakan coba lagi.</td></tr>');
                  modalLoading.addClass('d-none');
                  modalContent.removeClass('d-none');
              }
          });
      });
    });
  </script>
</body>
</html>
@endsection
