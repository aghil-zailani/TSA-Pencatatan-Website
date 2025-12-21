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

        .btn-send-report {
            background-color: #007bff;
            border-color: #007bff;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
        }
        .btn-send-report:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            color: #fff;
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
                <h3>{{ $judul }}</h3>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
                </ol>
            </nav>
            <div class="page-content">
                @if($barangTidakBagus->isEmpty())
                    <div class="alert alert-warning">
                        Tidak ada barang yang memenuhi kriteria atau belum memiliki QR Code.
                    </div>
                @else
                    <div class="alert alert-info">
                        <strong>Info:</strong> Hanya barang yang memiliki QR Code yang dapat diproses di laporan ini.
                    </div>
                @endif
                <section class="section">
                    <div class="card shadow h-md-50">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Daftar Barang Pemeliharaan</h4>
                            <form id="sendConditionReportForm" action="{{ route('staff_gudang.kirim_laporan_pengajuan') }}" method="POST">
                                @csrf
                                <input type="hidden" name="jenis_laporan" value="Laporan Kondisi Barang">
                                <button type="submit" class="btn btn-send-report">
                                    <i class="bi bi-send-fill me-2"></i> Kirim Laporan
                                </button>
                            </form>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>                                       
                                        @forelse ($barangTidakBagus as $item)
                                            @for ($j = 0; $j < $item->jumlah_barang; $j++)
                                                <tr>
                                                    <td><?= $i++ ?></td>
                                                    <td>{{ $item->nama_barang }}</td>
                                                    <td>{{ $item->tipe_barang }}</td>
                                                    <td>{{ $item->kondisi ?? 'N/A' }}</td>
                                                    <td>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : 'N/A' }}</td>
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

    {{-- Script JavaScript (untuk DataTables jika digunakan) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            $('#sendConditionReportForm').on('submit', function(e) {
                e.preventDefault(); 

                var form = $(this);
                var message = 'Apakah Anda yakin ingin mengirim laporan kondisi barang ini ke Supervisor?';

                Swal.fire({
                    title: 'Konfirmasi Pengiriman Laporan Kondisi',
                    text: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#ffc107', 
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Kirim!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.off('submit').submit(); 
                    }
                });
            });
        });
    </script>
@endsection