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
            <div class="page-content">
                <section class="section">      
                    <div class="card shadow h-md-50">
                        <div class="card-header">
                            <div class="btn btn-primary">
                                <span>Total Barang :</span>
                                <span class="total-barang-value">{{ $totalKeseluruhanBarang ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0"> {{-- Tambah class table-hover --}}
                                    <thead>
                                        <tr class="text-center"> {{-- Ganti align="center" dengan class Bootstrap --}}
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Jenis Barang</th>
                                            <th>Total Stok</th>
                                            <th>Kategori</th>
                                            <th>Media</th>
                                            <th>Berat</th>  
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @foreach ($barangAggregated as $item)
                                            <tr>
                                                <td><?= $i++ ?></td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->tipe_barang }}</td>
                                                <td class="text-center">{{ $item->total_stok }}</td>
                                                <td>{{ $item->tipe_barang_kategori ?? '-' }}</td>
                                                <td>{{ $item->media ?? '-' }}</td>
                                                <td>{{ $item->berat_display_formatted }}</td>                                               
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
    <div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="updatePriceForm" method="POST" action="{{ route('staff_gudang.updateHarga') }}">
                    @csrf
                    <div class="modal-header bg-primary text-white"> {{-- Gunakan class Bootstrap --}}
                        <h5 class="modal-title" id="editPriceModalLabel">Edit Harga Barang</h5>
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
                        <div class="mb-3">
                            <label for="edit_harga_jual" class="form-label">Harga Jual</label>
                            <input type="number" step="0.01" class="form-control" id="edit_harga_jual" name="harga_jual" placeholder="Masukkan Harga Jual">
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