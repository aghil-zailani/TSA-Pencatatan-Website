@extends('layouts/main')

@section('container')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul }}</title>
    <style>
        body { font-family: 'Poppins', sans-serif; color: #333; }
        h3, h4 { font-family: 'Poppins', sans-serif; font-weight: 700; color: #444; }
        .card { border-radius: 0.75rem; box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05); }
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
                            <h4 class="card-title">Daftar Barang Diterima</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Tipe Barang</th>
                                            <th>Kondisi</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @forelse ($barangDiterima as $item)
                                            @for ($j = 0; $j < $item->jumlah_barang; $j++)
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td>{{ $item->nama_barang }}</td>
                                                    <td>{{ $item->tipe_barang }}</td>
                                                    <td>{{ $item->kondisi ?? '-' }}</td> {{-- Kolom Kondisi --}}
                                                    <td>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}</td>
                                                    <td class="text-center">
                                                        @if ($item->qrCode && $item->qrCode->qr_code_path)
                                                            <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                                <i class="bi bi-check-circle"></i> QR Sudah Ada
                                                            </button>

                                                            <br>
                                                            <img src="{{ asset('storage/' . $item->qrCode->qr_code_path) }}" alt="QR Code" width="80">
                                                        @else
                                                            <button type="button" class="btn btn-generate-qr btn-success btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#qrCodeModal"
                                                                data-item-id="{{ $item->id_barang }}"
                                                                data-item-nama="{{ $item->nama_barang }}">
                                                                <i class="bi bi-qr-code"></i> Generate QR
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endfor
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">Belum ada barang yang diterima.</td>
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

    <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-sm">
            <div class="modal-header">
                <h5 class="modal-title" id="qrCodeModalLabel">
                <i class="bi bi-qr-code text-primary me-2"></i> QR Code Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body text-center">
                <p class="mb-2">Barang: <strong id="qrItemName"></strong></p>
                <div id="qrCodeContainer" class="my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="downloadQrBtn" href="#" class="btn btn-success">
                <i class="bi bi-download me-1"></i> Unduh QR Code
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Tutup
                </button>
            </div>
            </div>
        </div>
    </div>

    {{-- Script JavaScript --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
$(document).ready(function() {
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

    var qrShownHistory = {};

    $('#qrCodeModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var itemId = button.data('item-id');
        var itemName = button.data('item-nama');

        var qrCodeUrl = "{{ route('staff_gudang.generate_qrcode', ':id') }}".replace(':id', itemId);

        $('#qrCodeContainer').html(`
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        `);

        $.ajax({
            url: qrCodeUrl,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.status === 'exists') {
                    if (!qrShownHistory[itemId]) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'QR Sudah Ada',
                            text: data.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                        qrShownHistory[itemId] = true;
                    }
                }

                $('#qrItemName').text(itemName);
                $('#qrCodeContainer').html('<img src="' + data.url + '" alt="QR Code" class="img-fluid">');
                $('#downloadQrBtn').attr('href', data.url);
                $('#downloadQrBtn').attr('download', data.fileName);
            },
            error: function(xhr) {
                $('#qrCodeContainer').html('<p class="text-danger">Gagal memuat QR Code.</p>');
            }
        });
    });
});
</script>

@endsection