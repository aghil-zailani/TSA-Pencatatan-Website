@extends('layouts/main')

@section('container')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
    <style>
        /* Styling umum dari desain Anda */
        body { font-family: 'Poppins', sans-serif; color: #333; }
        h3, h4 { font-family: 'Poppins', sans-serif; font-weight: 700; color: #444; }
        .card { border-radius: 0.75rem; box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05); }
        .card-header { background-color: #f8f9fa; border-bottom: 1px solid #e9ecef; border-top-left-radius: 0.75rem; border-top-right-radius: 0.75rem; padding: 1.5rem; }
        .card-title { font-weight: 600; color: #343a40; }
        .table { --bs-table-bg: #fff; }
        .table thead th { font-family: 'Poppins', sans-serif; font-weight: 600; background-color: #e9ecef; color: #495057; text-align: center; }
        .table tbody td { font-family: 'Poppins', sans-serif; color: #495057; vertical-align: middle; }
        .btn-success-custom { background-color: #28a745; border-color: #28a745; color: #fff; }
        .btn-success-custom:hover { background-color: #218838; border-color: #1e7e34; }
        .btn-danger-custom { background-color: #dc3545; border-color: #dc3545; color: #fff; }
        .btn-danger-custom:hover { background-color: #c82333; border-color: #c82333; }
        .btn-detail-info {
            background-color: #007bff; /* Biru */
            border-color: #007bff;
            color: white;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
        }
        .btn-detail-info:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        /* Styling Modal Detail (tetap ada karena digunakan di validasi_laporan_detail) */
        #detailModal .modal-header { background-color: #007bff; color: white; }
        #detailModal .modal-header .btn-close { filter: invert(100%) grayscale(100%) brightness(200%); }
        .detail-item { margin-bottom: 1rem; }
        .detail-item label { font-weight: 600; font-family: 'Poppins', sans-serif; color: #555; display: block; margin-bottom: 0.25rem; }
        .detail-item p { font-family: 'Poppins', sans-serif; margin-bottom: 0; padding: 0.5rem 0; border-bottom: 1px dashed #eee; color: #333; }
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
                            <h4 class="card-title">Tabel Validasi Barang Masuk</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Laporan</th>
                                            <th>Jumlah Item</th>
                                            <th>Status</th>
                                            <th>Tanggal Pengajuan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($pengajuanPending as $laporan)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $laporan->nama_laporan }}</td>
                                                <td class="text-center">{{ $laporan->total_items }}</td>
                                                <td>
                                                    @if (ucfirst($laporan->status))
                                                    <span class="badge bg-primary">memproses</span>
                                                    @endif
                                                </td>
                                                <td>{{ $laporan->created_at ? $laporan->created_at->format('d M Y H:i') : 'N/A' }}</td>
                                                <td class="text-center">
                                                    {{-- Tombol Lihat & Validasi (mengarah ke halaman detail) --}}
                                                    <a href="{{ route('supervisor.validasi.laporan_detail', $laporan->report_id) }}" class="btn btn-primary btn-sm"> {{-- Gunakan btn-primary --}}
                                                        <i class="bi bi-eye-fill"></i> Lihat & Validasi
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Tidak ada laporan yang perlu divalidasi.</td> {{-- Colspan sesuai kolom di atas --}}
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

        {{-- SweetAlert untuk Notifikasi Sukses/Error --}}
        @if (session('message'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({ icon: "success", title: "Success!", text: "{{ session('message') }}", timer: 2500, showConfirmButton: false });
            </script>
        @endif
        @if (session('error'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({ icon: "error", title: "Error!", text: "{{ session('error') }}", timer: 2500, showConfirmButton: false });
            </script>
        @endif

    </body>

    </html>

    {{-- MODAL UNTUK MENAMPILKAN DETAIL BARANG (Ini ada di validasi_laporan_detail.blade.php) --}}
    {{-- Anda bisa menghapus blok ini dari file ini jika sudah ada di validasi_laporan_detail.blade.php --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="detailModalLabel">Detail Barang Masuk</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="detail-item">
                        <label>Nama Barang</label>
                        <p id="detail_nama_barang"></p>
                    </div>
                    <div class="detail-item">
                        <label>Kategori</label>
                        <p id="detail_kategori"></p>
                    </div>
                    <div class="detail-item">
                        <label>Tipe Barang</label>
                        <p id="detail_tipe_barang"></p>
                    </div>
                    <div class="detail-item">
                        <label>Jumlah</label>
                        <p id="detail_jumlah"></p>
                    </div>
                    <div class="detail-item">
                        <label>Satuan</label>
                        <p id="detail_satuan"></p>
                    </div>
                    <div class="detail-item">
                        <label>Kondisi Barang</label>
                        <p id="detail_kondisi"></p>
                    </div>
                    <div class="detail-item">
                        <label>Merek</label>
                        <p id="detail_merek"></p>
                    </div>
                    {{-- Detail Khusus APAR --}}
                    <div id="apar-details" style="display: none;">
                        <div class="detail-item">
                            <label>Berat</label>
                            <p id="detail_berat"></p>
                        </div>
                        <div class="detail-item">
                            <label>Tanggal Kadaluarsa</label>
                            <p id="detail_tgl_kadaluarsa"></p>
                        </div>
                    </div>
                    {{-- Detail Khusus Hydrant --}}
                    <div id="hydrant-details" style="display: none;">
                        <div class="detail-item">
                            <label>Ukuran Barang</label>
                            <p id="detail_ukuran"></p>
                        </div>
                        <div class="detail-item">
                            <label>Panjang</label>
                            <p id="detail_panjang"></p>
                        </div>
                        <div class="detail-item">
                            <label>Lebar</label>
                            <p id="detail_lebar"></p>
                        </div>
                        <div class="detail-item">
                            <label>Tinggi</label>
                            <p id="detail_tinggi"></p>
                        </div>
                    </div>
                    <div class="detail-item">
                        <label>Tanggal Pengajuan</label>
                        <p id="detail_tgl_pengajuan"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Script JavaScript --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#dataTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Semua"]
                ],
                "searching": true,
                "info": true,
                "paging": true,
                "ordering": false
            });

            // LOGIKA KONFIRMASI SWEETALERT UNTUK TERIMA/TOLAK
            $('.confirm-validation-form').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var button = form.find('button[type="submit"]');
                var confirmText = button.data('confirm-text') || 'Apakah Anda yakin?';
                var action = form.find('input[name="aksi"]').val();
                
                Swal.fire({
                    title: 'Konfirmasi Aksi',
                    text: confirmText,
                    icon: (action === 'terima') ? 'success' : 'warning',
                    showCancelButton: true,
                    confirmButtonColor: (action === 'terima') ? '#28a745' : '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: (action === 'terima') ? 'Ya, Terima!' : 'Ya, Tolak!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.off('submit').submit();
                    }
                });
            });

            // LOGIKA UNTUK MODAL LIHAT DETAIL
            $('.view-detail-btn').on('click', function() {
                // Ambil data dari data-attributes tombol
                var itemData = $(this).data();

                // Bersihkan tampilan modal sebelumnya
                $('#apar-details').hide();
                $('#hydrant-details').hide();
                
                // Isi data yang umum
                $('#detail_nama_barang').text(itemData.itemNama || 'N/A');
                $('#detail_kategori').text(itemData.itemKategori || 'N/A');
                $('#detail_tipe_barang').text(itemData.itemTipe || 'N/A');
                $('#detail_jumlah').text(itemData.itemJumlah || 'N/A');
                $('#detail_satuan').text(itemData.itemSatuan || 'N/A');
                $('#detail_kondisi').text(itemData.itemKondisi || 'N/A');
                $('#detail_merek').text(itemData.itemMerek || 'N/A');
                $('#detail_tgl_pengajuan').text(itemData.itemPengajuan || 'N/A');

                // Tampilkan detail khusus berdasarkan kategori
                if (itemData.itemKategori === 'APAR') {
                    $('#detail_berat').text(itemData.itemBerat || 'N/A');
                    $('#detail_tgl_kadaluarsa').text(itemData.itemTglKadaluarsa || 'N/A');
                    $('#apar-details').show();
                } else if (itemData.itemKategori === 'Hydrant') {
                    $('#detail_ukuran').text(itemData.itemUkuran || 'N/A');
                    $('#detail_panjang').text(itemData.itemPanjang || 'N/A');
                    $('#detail_lebar').text(itemData.itemLebar || 'N/A');
                    $('#detail_tinggi').text(itemData.itemTinggi || 'N/A');
                    $('#hydrant-details').show();
                }
            });
        });
    </script>
@endsection