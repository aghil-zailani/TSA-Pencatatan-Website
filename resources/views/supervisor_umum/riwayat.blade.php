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
                        <h4 class="card-title">Tabel Riwayat Pemeliharaan</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="riwayatTable" width="100%">
                                <thead>
                                    <tr class="text-center">
                                        <th>ID QR</th>
                                        <th>Role</th>
                                        <th>Nama Barang</th>
                                        <th>Tipe Barang</th>
                                        <th>Tanggal Inspeksi</th>
                                        <th>Lokasi Alat</th>
                                        <th>Foto</th>
                                        <th>Kondisi Fisik</th>
                                        <th>Selang</th>
                                        <th>Pressure Gauge</th>
                                        <th>Safety Pin</th>
                                        <th>Tindakan</th>
                                        <th>Dibuat Pada</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($riwayat as $laporan)
                                        <tr>
                                            <td>{{ $laporan->id_qr }}</td>
                                            <td>{{ $laporan->created_by_role }}</td>
                                            <td>{{ $laporan->nama_barang }}</td>
                                            <td>{{ $laporan->tipe_barang }}</td>
                                            <td>{{ \Carbon\Carbon::parse($laporan->tanggal_inspeksi)->format('d M Y') }}</td>
                                            <td>{{ $laporan->lokasi_alat }}</td>
                                            <td>
                                                @if ($laporan->foto)
                                                    <img src="{{ asset('storage/' . $laporan->foto) }}" alt="Foto"
                                                        style="max-width: 80px;">
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>{{ $laporan->kondisi_fisik }}</td>
                                            <td>{{ $laporan->selang ?? '-' }}</td>
                                            <td>{{ $laporan->pressure_gauge ?? '-' }}</td>
                                            <td>{{ $laporan->safety_pin ?? '-' }}</td>
                                            <td>{{ $laporan->tindakan }}</td>
                                            <td>{{ $laporan->created_at ? $laporan->created_at->format('d M Y H:i') : '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="20" class="text-center text-muted">Tidak ada riwayat pemeliharaan.</td>
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
        $(document).ready(function () {
            $('#riwayatTable').DataTable({
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
