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

        <section class="section">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="card-title">Tabel Validasi Pemeliharaan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="pemeliharaanTable" width="100%">
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
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ ucfirst($laporan->status) }}</span>
                                        </td>
                                        <td>{{ $laporan->created_at ? $laporan->created_at->format('d M Y H:i') : 'N/A' }}</td>
                                        <td class="text-center">
                                            <a href="#" class="btn btn-primary btn-sm">
                                                <i class="bi bi-eye-fill"></i> Lihat & Validasi
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Tidak ada laporan pemeliharaan yang perlu divalidasi.</td>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#pemeliharaanTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
            searching: true,
            info: true,
            paging: true,
            ordering: false
        });
    });
</script>
@endsection
