@extends('layouts/main')

@section('container')
    <div id="app">
        <div id="main">
            <div class="page-heading">
                <h3>{{ $judul }}</h3>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
                </ol>
            </nav>

            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

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
                                        <th>Nama Barang</th>
                                        <th>Tipe</th>
                                        <th>Lokasi</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($pengajuanPending as $laporan)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $laporan->nama_barang }}</td>
                                            <td>{{ $laporan->tipe_barang }}</td>
                                            <td>{{ $laporan->lokasi_alat }}</td>
                                            <td>{{ \Carbon\Carbon::parse($laporan->tanggal_inspeksi)->format('d M Y') }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-primary btnValidasi"
                                                    data-id="{{ $laporan->id_laporan_pemeliharaan }}" type="button">
                                                    <i class="bi bi-check-circle"></i> Validasi
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Tidak ada laporan pemeliharaan.</td>
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

    <!-- Modal Validasi -->
    <div class="modal fade" id="modalValidasi" tabindex="-1" aria-labelledby="modalValidasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form id="formValidasi" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalValidasiLabel">Validasi Laporan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p>Memuat data...</p>
                        </div>

                        <!-- Content -->
                        <div id="laporanDetail" style="display: none;">
                            <p><strong>Nama Barang:</strong> <span id="nama_barang_text"></span></p>
                            <p><strong>Tipe:</strong> <span id="tipe_barang_text"></span></p>
                            <p><strong>Lokasi:</strong> <span id="lokasi_alat_text"></span></p>
                            <p><strong>Tanggal Inspeksi:</strong> <span id="tanggal_text"></span></p>
                            <p><strong>Kondisi Fisik:</strong> <span id="kondisi_text"></span></p>
                            <p><strong>Tindakan:</strong> <span id="tindakan_text"></span></p>
                            <div id="tambahan_apar_fields" style="display: none;">
                                <p><strong>Selang:</strong> <span id="selang_text"></span></p>
                                <p><strong>Pressure Gauge:</strong> <span id="gauge_text"></span></p>
                                <p><strong>Safety Pin:</strong> <span id="pin_text"></span></p>
                            </div>
                            <p><strong>Foto:</strong><br>
                                <img id="foto_preview" src="" alt="Foto" width="200" class="img-thumbnail" />
                            </p>
                            <hr>

                            <div class="mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select" required>
                                    <option value="">-- Pilih Status --</option>
                                    <option value="Diterima">Diterima</option>
                                    <option value="Ditolak">Ditolak</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="catatan_validasi" class="form-label">Catatan</label>
                                <textarea name="catatan_validasi" id="catatan_validasi" rows="3" class="form-control"
                                    placeholder="Catatan validasi (opsional)..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>Kirim Validasi</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <!-- jQuery (pastikan ini dimuat sebelum yang lain) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function () {
            // Initialize DataTable
            $('#pemeliharaanTable').DataTable({
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                searching: true,
                info: true,
                paging: true,
                ordering: false,
                language: {
                    search: "Pencarian:",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(difilter dari _MAX_ total data)",
                    paginate: {
                        first: "Pertama",
                        last: "Terakhir",
                        next: "Selanjutnya",
                        previous: "Sebelumnya"
                    },
                    emptyTable: "Tidak ada data yang tersedia"
                }
            });

            // Modal Validasi Handler
            $('.btnValidasi').on('click', function () {
                const id = $(this).data('id');
                console.log('ID yang diklik:', id);

                const modal = new bootstrap.Modal(document.getElementById('modalValidasi'));

                // Validasi ID
                if (!id) {
                    console.error('ID tidak ditemukan pada button');
                    alert('Error: ID laporan tidak valid');
                    return;
                }

                // Reset form and show loading
                $('#formValidasi')[0].reset();
                $('#formValidasi').attr('action', `/supervisor/pemeliharaan-validasi/${id}`);
                $('#loadingIndicator').show();
                $('#laporanDetail').hide();
                $('#submitBtn').prop('disabled', true);

                // Show modal
                modal.show();

                // Fetch data via AJAX
                const ajaxUrl = `/supervisor/laporan/${id}`;
                console.log('AJAX URL:', ajaxUrl);

                $.ajax({
                    url: ajaxUrl,
                    type: 'GET',
                    dataType: 'json',
                    timeout: 10000,
                    beforeSend: function (xhr) {
                        console.log('Mengirim request ke:', ajaxUrl);
                    },
                    success: function (data) {
                        console.log('Data berhasil diterima:', data);

                        // Hide loading and show content
                        $('#loadingIndicator').hide();
                        $('#laporanDetail').show();

                        // Populate modal with data
                        $('#nama_barang_text').text(data.nama_barang || '-');
                        $('#tipe_barang_text').text(data.tipe_barang || '-');
                        $('#lokasi_alat_text').text(data.lokasi_alat || '-');
                        $('#tanggal_text').text(data.tanggal_inspeksi_formatted || data.tanggal_inspeksi || '-');
                        $('#kondisi_text').text(data.kondisi_fisik || '-');
                        $('#tindakan_text').text(data.tindakan || '-');

                        // Handle foto
                        let fotoSrc = '{{ asset('storage') }}/foto_laporan/default.jpg';

                        if (data.foto) {
                            fotoSrc = '{{ asset('storage') }}/' + data.foto;
                        }


                        $('#foto_preview').attr('src', fotoSrc);

                        // Show APAR specific fields if needed
                        if (data.tipe_barang && data.tipe_barang.toLowerCase() === 'apar') {
                            $('#tambahan_apar_fields').show();
                            $('#selang_text').text(data.selang || '-');
                            $('#gauge_text').text(data.pressure_gauge || '-');
                            $('#pin_text').text(data.safety_pin || '-');
                        } else {
                            $('#tambahan_apar_fields').hide();
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('AJAX Error Details:');
                        console.error('Status:', status);
                        console.error('Error:', error);
                        console.error('Response Status:', xhr.status);
                        console.error('Response Text:', xhr.responseText);

                        $('#loadingIndicator').hide();

                        let errorMessage = 'Gagal memuat data laporan.';

                        switch (xhr.status) {
                            case 404:
                                errorMessage = 'Data laporan tidak ditemukan.';
                                break;
                            case 500:
                                errorMessage = 'Terjadi kesalahan server.';
                                break;
                            case 0:
                                errorMessage = 'Tidak dapat terhubung ke server.';
                                break;
                            default:
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }
                        }

                        if (xhr.responseJSON && xhr.responseJSON.debug_id) {
                            errorMessage += ` (Debug ID: ${xhr.responseJSON.debug_id})`;
                        }

                        if (xhr.responseJSON && xhr.responseJSON.available_ids) {
                            console.log('ID yang tersedia:', xhr.responseJSON.available_ids);
                        }

                        $('#laporanDetail').html(`
                                <div class="alert alert-danger">
                                    <strong>Error:</strong> ${errorMessage}
                                    <br><small>Silakan coba lagi atau hubungi administrator.</small>
                                    <br><small class="text-muted">Request ID: ${id}</small>
                                </div>
                            `).show();
                    }
                });
            });

            // Enable submit button when status is selected
            $('#status').on('change', function () {
                if ($(this).val()) {
                    $('#submitBtn').prop('disabled', false);
                } else {
                    $('#submitBtn').prop('disabled', true);
                }
            });

            // Form validation before submit
            $('#formValidasi').on('submit', function (e) {
                const status = $('#status').val();
                if (!status) {
                    e.preventDefault();
                    alert('Silakan pilih status validasi terlebih dahulu.');
                    return false;
                }
            });
        });
    </script>

    @if(session('success'))
        <script>
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            });
        </script>
    @endif

@endpush
