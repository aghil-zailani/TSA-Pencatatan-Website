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
        .btn-success-custom { background-color: #28a745; border-color: #28a745; color: #fff; }
        .btn-success-custom:hover { background-color: #218838; border-color: #1e7e34; color: #fff; }
        .btn-danger-custom { background-color: #dc3545; border-color: #dc3545; color: #fff; }
        .btn-danger-custom:hover { background-color: #c82333; border-color: #c82333; color: #fff; }
        .btn-detail-info {
            background-color: #007bff; 
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

        #detailModal .modal-header {
            background-color: #007bff; 
            color: white;
        }
        #detailModal .modal-header .btn-close {
            filter: invert(100%) grayscale(100%) brightness(200%);
        }
        .detail-item {
            margin-bottom: 1rem;
        }
        .detail-item label {
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            color: #555;
            display: block;
            margin-bottom: 0.25rem;
        }
        .detail-item p {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 0;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #eee;
            color: #333;
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
                    <li class="breadcrumb-item"><a href="{{ route('supervisor.riwayat') }}">Riwayat</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
                </ol>
            </nav>
            <div class="page-content">
                <section class="section">
                    <div class="card shadow h-md-50">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">{{ $nama_laporan }} - {{ $report_id }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>Tipe Barang</th>
                                            <th>Jumlah</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Satuan</th>
                                            <th>Kondisi</th>
                                            <th>Berat</th>
                                            <th>Media</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($itemsInReport as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->tipe_barang_kategori }}</td>
                                                <td>{{ $item->tipe_barang }}</td>
                                                <td class="text-center">{{ $item->jumlah_barang }}</td>
                                                <td>{{ $item->created_at ? $item->created_at->format('d M Y H:i') : 'N/A' }}</td>
                                                <td>{{ $item->satuan ?? 'N/A' }}</td>
                                                <td>{{ $item->kondisi_barang ?? 'N/A' }}</td>
                                                <td>{{ $item->berat ?? 'N/A' }}</td>
                                                <td>{{ $item->media ?? 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted">Tidak ada item dalam laporan ini.</td>
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

    </body>

    </html>

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
            });
        });
    </script>
@endsection