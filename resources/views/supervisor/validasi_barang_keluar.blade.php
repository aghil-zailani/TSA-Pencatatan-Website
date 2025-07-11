@extends('layouts/main')

@section('container')
<div id="app">
    <div id="main">
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
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Tabel Validasi Barang Keluar</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="dataTable" class="table table-striped table-bordered" width="100%">
                                <thead class="text-center">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Laporan</th>
                                        <th>Jumlah Item</th>
                                        <th>Status</th>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($keluars as $index => $keluar)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>Laporan Barang Keluar</td>
                                        <td class="text-center">{{ $keluar->jumlah_barang }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">memproses</span>
                                        </td>
                                        <td>{{ $keluar->created_at }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('supervisor.validasi.keluar.detail', $keluar->id_transaksi) }}" class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye-fill"></i> Lihat & Validasi
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada pengajuan barang keluar.</td>
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

{{-- Datatables Scripts --}}
<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "lengthMenu": [10, 25, 50, 100],
            "pageLength": 10,
            "ordering": false
        });
    });
</script>
@if (session('message'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('message') }}",
            timer: 2500,
            showConfirmButton: false
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: "{{ session('message') }}",
            timer: 2500,
            showConfirmButton: false
        });
    </script>
@endif

@endsection
