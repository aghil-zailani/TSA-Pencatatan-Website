@extends('layouts/main')

@section('container')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
    <style>
        body { font-family: 'Poppins', sans-serif; color: #333; }
        h3, h4 { font-weight: 700; }
        .card { border-radius: 0.75rem; box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.05); }
        .card-header { background-color: #f8f9fa; padding: 1.5rem; border-bottom: 1px solid #e9ecef; }
        .btn-success-custom { background-color: #28a745; border-color: #28a745; color: #fff; }
        .btn-danger-custom { background-color: #dc3545; border-color: #dc3545; color: #fff; }
        .btn-success-custom:hover { background-color: #218838; }
        .btn-danger-custom:hover { background-color: #c82333; }
        table th, table td { text-align: center; vertical-align: middle; }
        .breadcrumb { background: none; padding: 0; }
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
                    <li class="breadcrumb-item"><a href="{{ route('supervisor.validasi.barang_keluar') }}">Validasi Barang Keluar</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Laporan</li>
                </ol>
            </nav>

            <div class="page-content">
                <section class="section">
                    <div class="card shadow h-md-50">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0">Laporan Barang Keluar</h4>
                            <div>
                                <form action="{{ route('supervisor.validasi.keluar.terima', $keluar->id_transaksi) }}" method="POST" class="d-inline confirm-validation-form">
                                    @csrf
                                    <button type="submit" class="btn btn-success-custom btn-sm" data-confirm-text="Apakah yakin ingin MENERIMA laporan ini?">
                                        Terima
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger-custom btn-sm" data-bs-toggle="modal" data-bs-target="#tolakModal">
                                    Tolak
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Kategori</th>
                                            <th>Tipe Barang</th>
                                            <th>Jumlah</th>
                                            <th>Tanggal Keluar</th>
                                            <th>Satuan</th>
                                            <th>Kondisi</th>
                                            <th>Berat</th>
                                            <th>Tanggal Kadaluarsa</th>
                                            <th>Merek</th>
                                            <th>Ukuran</th>
                                            <th>Panjang</th>
                                            <th>Lebar</th>
                                            <th>Tinggi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>1</td>
                                            <td>{{ $keluar->barang->nama_barang ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->kategori->nama_kategori ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->tipe_barang ?? 'N/A' }}</td>
                                            <td>{{ $keluar->jumlah_barang }}</td>
                                            <td>{{ $keluar->created_at ? $keluar->created_at->format('d M Y H:i') : 'N/A' }}</td>
                                            <td>{{ $keluar->barang->satuan ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->kondisi ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->berat ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->tgl_kadaluarsa ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->merek ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->ukuran ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->panjang ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->lebar ?? 'N/A' }}</td>
                                            <td>{{ $keluar->barang->tinggi ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Modal Tolak -->
        <div class="modal fade" id="tolakModal" tabindex="-1" aria-labelledby="tolakModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('supervisor.validasi.keluar.tolak', $keluar->id_transaksi) }}" method="POST" class="confirm-validation-form">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">Tolak Laporan</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label for="catatan_penolakan" class="fw-semibold mb-2">Alasan Penolakan</label>
                            <textarea name="catatan_penolakan" class="form-control" rows="4" placeholder="Tulis alasan penolakan..." required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Tolak Laporan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if (session('message'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({ icon: "success", title: "Sukses!", text: "{{ session('message') }}", timer: 2500, showConfirmButton: false });
            </script>
        @endif
        @if (session('error'))
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            <script>
                Swal.fire({ icon: "error", title: "Error!", text: "{{ session('error') }}", timer: 2500, showConfirmButton: false });
            </script>
        @endif
    </body>

    {{-- Script --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $('.confirm-validation-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var btn = form.find('button[type="submit"]');
            var confirmText = btn.data('confirm-text') || 'Apakah Anda yakin?';

            Swal.fire({
                title: 'Konfirmasi',
                text: confirmText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Lanjut!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.off('submit').submit();
                }
            });
        });
    </script>
</html>
@endsection
