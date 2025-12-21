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
                    <li class="breadcrumb-item"><a href="{{ route('supervisor.validasi.barang_keluar') }}">Validasi Barang Keluar</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
                </ol>
            </nav>
            <div class="page-content">
                <section class="section">
                    <div class="card shadow h-md-50">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Laporan Barang Keluar</h4>
                            <div>
                                @php
                                    $semuaSelesai = $keluar->every(fn($item) => in_array($item->status, ['diterima', 'ditolak']));
                                @endphp

                                @if(!$keluar->isEmpty() && !$semuaSelesai)
                                <form action="{{ route('supervisor.validasi.pengajuan.keluar') }}" method="POST" class="d-inline confirm-validation-form">
                                    @csrf
                                    <input type="hidden" name="report_id" value="{{ $reportId }}">
                                    <input type="hidden" name="aksi" value="terima">
                                    <button type="submit" class="btn btn-success-custom btn-sm"
                                        data-confirm-text="Apakah Anda yakin ingin MENERIMA seluruh laporan ini?">
                                        Terima Semua
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger-custom btn-sm" data-bs-toggle="modal" data-bs-target="#tolakModal">
                                    Tolak Semua
                                </button>
                                @endif

                                @php
                                    $semuaAccept = $keluar->every(fn($item) => $item->status === 'diterima');
                                    $semuaReject = $keluar->every(fn($item) => $item->status === 'ditolak');
                                @endphp

                                @if ($semuaAccept)
                                    <div class="btn btn-success">Laporan Sudah Diterima</div>
                                @elseif ($semuaReject)
                                    <div class="btn btn-danger">Laporan Ditolak</div>
                                @endif

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Jumlah Barang</th>
                                            <th>Tujuan</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($keluar as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->jumlah_barang  }}</td>
                                                <td>{{ $item->tujuan }}</td>
                                                <td>{{ $item->keterangan }}</td>
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

        <!-- Modal Tolak Laporan -->
        <div class="modal fade" id="tolakModal" tabindex="-1" aria-labelledby="tolakModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('supervisor.validasi.pengajuan.keluar') }}" method="POST" class="confirm-validation-form">
                @csrf
                <input type="hidden" name="report_id" value="{{ $reportId }}">
                <input type="hidden" name="aksi" value="tolak">
                <div class="modal-content border-0 shadow-lg rounded">
                    <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white" id="tolakModalLabel">Tolak Laporan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                    <p class="mb-3">Silakan berikan alasan penolakan laporan ini:</p>
                    <div class="form-group">
                        <label for="catatan_penolakan" class="form-label fw-semibold">Catatan Penolakan</label>
                        <textarea class="form-control rounded" name="catatan_penolakan" id="catatan_penolakan" rows="4" placeholder="Contoh: Data barang tidak sesuai atau stok tidak valid." required></textarea>
                    </div>
                    </div>
                    <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Laporan</button>
                    </div>
                </div>
                </form>
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
        });
    </script>
@endsection