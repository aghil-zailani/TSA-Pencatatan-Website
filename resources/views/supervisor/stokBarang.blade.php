@extends('layouts/main') {{-- Pastikan ini mengimpor Bootstrap CSS dan JS --}}

@section('container')
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Stok Barang</title>
    <style>
        /* CSS custom yang mungkin tidak sepenuhnya di-cover oleh Bootstrap atau untuk override spesifik */
        body {
            font-family: 'Poppins', sans-serif;
        }
        .table thead th, .table tbody td {
            font-family: 'Poppins', sans-serif;
            vertical-align: middle;
        }
        .table thead th {
            text-align: center;
        }
        .export-buttons-container {
            display: flex;
            gap: 0.75rem; /* Jarak antar tombol */
            justify-content: flex-end; /* Tombol di kanan */
            align-items: center; /* Tombol di tengah vertikal */
            height: 100%; /* Pastikan tingginya sama dengan kotak total barang */
        }
        .export-buttons-container .btn {
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.75rem 1.25rem; /* Padding tombol yang lebih pas */
        }
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
                <h3>Monitoring Stok Barang</h3>
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
                            <div class="row align-items-center g-3">

                                <!-- Total Barang -->
                                <div class="col-md-2">
                                    <div class="btn btn-primary w-100">
                                        Total Barang : <strong>{{ $totalKeseluruhanBarang ?? 0 }}</strong>
                                    </div>
                                </div>

                                <!-- Filter Kolom Dropdown -->
                                <div class="col-md-2">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary dropdown-toggle w-100 text-start" type="button" id="filterColumnsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            Pilih Kolom Ditampilkan
                                        </button>
                                        <form method="GET" class="dropdown-menu p-3" aria-labelledby="filterColumnsDropdown" style="min-width: 300px;">
                                            <label class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="columns[]" value="nama_barang" {{ in_array('nama_barang', $selectedColumns) ? 'checked' : '' }} >
                                                Nama Barang
                                            </label>
                                            <label class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="columns[]" value="tipe_barang" {{ in_array('tipe_barang', $selectedColumns) ? 'checked' : '' }} >
                                                Jenis Barang
                                            </label>
                                            <label class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="columns[]" value="total_stok" {{ in_array('total_stok', $selectedColumns) ? 'checked' : '' }} >
                                                Total Stok
                                            </label>
                                            <label class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="columns[]" value="berat_barang" {{ in_array('berat_barang', $selectedColumns) ? 'checked' : '' }} >
                                                Berat
                                            </label>
                                            <label class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="columns[]" value="harga_beli" {{ in_array('harga_beli', $selectedColumns) ? 'checked' : '' }}>
                                                Harga Beli
                                            </label>
                                            <label class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" name="columns[]" value="harga_jual" {{ in_array('harga_jual', $selectedColumns) ? 'checked' : '' }}>
                                                Harga Jual
                                            </label>
                                            <button type="submit" class="btn btn-primary btn-sm w-100">Terapkan</button>
                                        </form>
                                        
                                    </div>
                                </div>

                                <!-- Tombol Export -->
                                <div class="col-md-8 text-md-end">
                                    <div class="d-inline-flex flex-wrap gap-2">
                                        <a href="{{ route('supervisor.exportExcel') }}" class="btn btn-success">
                                            <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
                                        </a>
                                        <a href="{{ route('supervisor.exportPdf') }}" class="btn btn-danger">
                                            <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
                                        </a>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0"> {{-- Tambah class table-hover --}}
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            @if(in_array('nama_barang', $selectedColumns))
                                                <th>Nama Barang</th>
                                            @endif
                                            @if(in_array('tipe_barang', $selectedColumns))
                                                <th>Jenis Barang</th>
                                            @endif
                                            @if(in_array('total_stok', $selectedColumns))
                                                <th>Total Stok</th>
                                            @endif
                                            @if(in_array('berat_barang', $selectedColumns))
                                                <th>Berat</th>
                                            @endif
                                            @if(in_array('harga_beli', $selectedColumns))
                                                <th>Harga Beli</th>
                                            @endif
                                            @if(in_array('harga_jual', $selectedColumns))
                                                <th>Harga Jual</th>
                                            @endif
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 1; @endphp
                                        @foreach ($barangAggregated as $item)
                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                @if(in_array('nama_barang', $selectedColumns))
                                                    <td>{{ $item->nama_barang }}</td>
                                                @endif
                                                @if(in_array('tipe_barang', $selectedColumns))
                                                    <td>{{ $item->tipe_barang }}</td>
                                                @endif
                                                @if(in_array('total_stok', $selectedColumns))
                                                    <td>{{ $item->total_stok }}</td>
                                                @endif
                                                @if(in_array('berat_barang', $selectedColumns))
                                                    <td>{{ $item->berat_barang }} kg</td>
                                                @endif
                                                @if(in_array('harga_beli', $selectedColumns))
                                                    <td>{{ number_format($item->harga_beli, 2) }}</td>
                                                @endif
                                                @if(in_array('harga_jual', $selectedColumns))
                                                    <td>{{ number_format($item->harga_jual, 2) }}</td>
                                                @endif
                                                <td>
                                                    <button
                                                        class="btn btn-primary edit-price-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editPriceModal"
                                                        data-nama-barang="{{ $item->nama_barang }}"
                                                        data-tipe-barang="{{ $item->tipe_barang }}"
                                                        data-berat-barang="{{ $item->berat_barang }}"
                                                        data-harga-beli="{{ $item->harga_beli }}"
                                                        data-harga-jual="{{ $item->harga_jual }}">
                                                        Edit Harga
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
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
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "{{ session('message') }}",
                    timer: 2500,
                    showConfirmButton: false
                });
            </script>
        @endif
        @if (session('error'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "{{ session('error') }}",
                    timer: 2500,
                    showConfirmButton: false
                });
            </script>
        @endif

    </body>

    </html>

    {{-- MODAL EDIT HARGA BARANG --}}
    <div class="modal fade" id="editPriceModal" tabindex="-1" role="dialog" aria-labelledby="editPriceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="updatePriceForm" method="POST" action="{{ route('supervisor.updateHarga') }}">
                    @csrf
                    <div class="modal-header bg-primary ">
                        <h5 class="modal-title text-white" id="editPriceModalLabel">Edit Harga Beli Barang</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button> {{-- btn-close-white --}}
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="nama_barang" id="edit_nama_barang">
                        <input type="hidden" name="tipe_barang" id="edit_tipe_barang">
                        <input type="hidden" name="berat_barang" id="edit_berat_barang">

                        <div class="mb-3">
                            <label for="edit_harga_beli" class="form-label">Harga Beli</label>
                            <input type="number" step="0.01" class="form-control" id="edit_harga_beli" name="harga_beli" placeholder="Masukkan Harga Beli">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script untuk mengisi modal dan mengirim form AJAX --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            var table = $('#dataTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Semua"]
                ],
                "searching": true, // Aktifkan fitur pencarian bawaan DataTables
                "info": true,
                "paging": true
            });

            // Logika untuk mengisi modal edit harga (tetap sama)
            $('.edit-price-btn').on('click', function() {
                var namaBarang = $(this).data('nama-barang');
                var tipeBarang = $(this).data('tipe-barang');
                var beratBarang = $(this).data('berat-barang');
                var hargaBeli = $(this).data('harga-beli');
                var hargaJual = $(this).data('harga-jual');

                $('#edit_nama_barang').val(namaBarang);
                $('#edit_tipe_barang').val(tipeBarang); 
                $('#edit_berat_barang').val(beratBarang);
                $('#edit_harga_beli').val(hargaBeli);
                $('#edit_harga_jual').val(hargaJual);
            });

            // Logika ketika form update harga disubmit (tetap sama)
            $('#updatePriceForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        var stockModal = bootstrap.Modal.getInstance(document.getElementById('editPriceModal'));
                        if (stockModal) { stockModal.hide(); }
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Harga berhasil diperbarui.',
                            timer: 2500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var stockModal = bootstrap.Modal.getInstance(document.getElementById('editPriceModal'));
                        if (stockModal) { stockModal.hide(); }
                        var errorMessage = 'Terjadi kesalahan. Mohon coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                });
            });
        });
    </script>
@endsection