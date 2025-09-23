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

    <!-- Modal Validasi dengan Desain Modern -->
    <div class="modal fade" id="modalValidasi" tabindex="-1" aria-labelledby="modalValidasiLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form id="formValidasi" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-clipboard-check me-2 fs-4"></i>
                            <h5 class="modal-title mb-0" id="modalValidasiLabel">Validasi Laporan Pemeliharaan</h5>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-0">
                        <!-- Loading Indicator -->
                        <div id="loadingIndicator" class="text-center py-5">
                            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted mb-0">Memuat data laporan...</p>
                        </div>

                        <!-- Content -->
                        <div id="laporanDetail" style="display: none;">
                            <div class="row g-0">
                                <!-- Bagian Kiri - Detail Laporan -->
                                <div class="col-md-8 border-end">
                                    <div class="p-4">
                                        <h6 class="text-primary mb-3 fw-bold">
                                            <i class="bi bi-info-circle me-2"></i>Detail Laporan
                                        </h6>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <label class="form-label text-muted small fw-semibold">NAMA
                                                        BARANG</label>
                                                    <div class="info-value" id="nama_barang_text">-</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <label class="form-label text-muted small fw-semibold">TIPE</label>
                                                    <div class="info-value" id="tipe_barang_text">-</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <label class="form-label text-muted small fw-semibold">LOKASI</label>
                                                    <div class="info-value" id="lokasi_alat_text">-</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <label class="form-label text-muted small fw-semibold">TANGGAL
                                                        INSPEKSI</label>
                                                    <div class="info-value" id="tanggal_text">-</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <label class="form-label text-muted small fw-semibold">KONDISI
                                                        FISIK</label>
                                                    <div class="info-value" id="kondisi_text">-</div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="info-item">
                                                    <label class="form-label text-muted small fw-semibold">TINDAKAN</label>
                                                    <div class="info-value" id="tindakan_text">-</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="info-item">
                                                    <label class="form-label text-muted small fw-semibold">CATATAN
                                                        TINDAKAN</label>
                                                    <div class="info-value" id="catatan_tindakan_text">-</div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- APAR Specific Fields -->
                                        <div id="tambahan_apar_fields" class="mt-4" style="display: none;">
                                            <h6 class="text-warning mb-3 fw-bold">
                                                <i class="bi bi-fire me-2"></i>Detail APAR
                                            </h6>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="info-item">
                                                        <label
                                                            class="form-label text-muted small fw-semibold">SELANG</label>
                                                        <div class="info-value" id="selang_text">-</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-item">
                                                        <label class="form-label text-muted small fw-semibold">PRESSURE
                                                            GAUGE</label>
                                                        <div class="info-value" id="gauge_text">-</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-item">
                                                        <label class="form-label text-muted small fw-semibold">SAFETY
                                                            PIN</label>
                                                        <div class="info-value" id="pin_text">-</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Bagian Kanan - Foto dan Validasi -->
                                <div class="col-md-4">
                                    <div class="p-4">
                                        <!-- Foto Section -->
                                        <div class="mb-4">
                                            <h6 class="text-primary mb-3 fw-bold">
                                                <i class="bi bi-camera me-2"></i>Foto Laporan
                                            </h6>
                                            <div class="position-relative">
                                                <img id="foto_preview" src="" alt="Foto Laporan"
                                                    class="img-fluid rounded shadow-sm w-100"
                                                    style="max-height: 250px; object-fit: cover;" />
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <button type="button" class="btn btn-sm btn-light rounded-circle"
                                                        onclick="openImageModal()" title="Lihat gambar penuh">
                                                        <i class="bi bi-arrows-fullscreen"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Validation Section -->
                                        <div>
                                            <h6 class="text-success mb-3 fw-bold">
                                                <i class="bi bi-check-circle me-2"></i>Validasi
                                            </h6>

                                            <div class="mb-3">
                                                <label for="status" class="form-label fw-semibold">
                                                    Status Validasi <span class="text-danger">*</span>
                                                </label>
                                                <select name="status" id="status" class="form-select form-select-lg"
                                                    required>
                                                    <option value="">-- Pilih Status --</option>
                                                    <option value="Diterima">✅ Diterima</option>
                                                    <option value="Ditolak">❌ Ditolak</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label for="catatan_validasi" class="form-label fw-semibold">Catatan
                                                    Validasi</label>
                                                <textarea name="catatan_validasi" id="catatan_validasi" rows="4"
                                                    class="form-control"
                                                    placeholder="Tambahkan catatan validasi (opsional)..."></textarea>
                                                <div class="form-text">
                                                    <small class="text-muted">Berikan penjelasan jika diperlukan</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-0">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg px-4" id="submitBtn" disabled>
                            <i class="bi bi-send me-2"></i>Kirim Validasi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Fullscreen Image -->
    <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center">
                    <img id="fullscreenImage" src="" alt="Foto Laporan" class="img-fluid rounded" />
                    <button type="button" class="btn btn-light position-absolute top-0 end-0 m-3 rounded-circle"
                        data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Custom Styles untuk Modal -->
    <style>
        .info-item {
            margin-bottom: 1rem;
        }

        .info-value {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #e9ecef;
            font-weight: 500;
            color: #495057;
        }

        .modal-xl {
            max-width: 1200px;
        }

        .btn-lg {
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
        }

        #status option {
            padding: 10px;
            font-size: 1rem;
        }

        .form-select-lg {
            padding: 0.75rem 1rem;
            font-size: 1.1rem;
        }

        .modal-content {
            border-radius: 1rem;
            overflow: hidden;
        }

        .modal-header {
            padding: 1.5rem;
        }

        .border-end {
            border-right: 2px solid #e9ecef !important;
        }

        /* Smooth transitions */
        .modal.fade .modal-dialog {
            transform: translate(0, -50px);
            transition: all 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: translate(0, 0);
        }

        /* Image hover effect */
        #foto_preview {
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        #foto_preview:hover {
            transform: scale(1.05);
        }
    </style>
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
                        $('#catatan_tindakan_text').text(data.catatan_tindakan || '-');

                        // Handle foto
                        let fotoSrc = '{{ asset('storage') }}/foto_laporan/default.jpg';

                        if (data.foto) {
                            fotoSrc = '{{ asset('storage') }}/' + data.foto;
                        }

                        $('#foto_preview').attr('src', fotoSrc);
                        $('#fullscreenImage').attr('src', fotoSrc);

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
                                <div class="p-4">
                                    <div class="alert alert-danger text-center">
                                        <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3"></i>
                                        <h5 class="alert-heading">Oops! Terjadi Kesalahan</h5>
                                        <p class="mb-0">${errorMessage}</p>
                                        <hr>
                                        <small class="text-muted">Request ID: ${id}</small>
                                        <br><small class="text-muted">Silakan coba lagi atau hubungi administrator.</small>
                                    </div>
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

            // Image click handler untuk foto preview
            $('#foto_preview').on('click', function () {
                openImageModal();
            });
        });

        // Function untuk membuka modal fullscreen image
        function openImageModal() {
            const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
            imageModal.show();
        }
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
