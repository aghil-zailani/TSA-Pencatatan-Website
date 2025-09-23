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
        /* Styling umum untuk kartu */
        .master-data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
        }
        @media (max-width: 768px) {
            .master-data-grid { grid-template-columns: 1fr; }
        }
        .master-data-card-link {
            text-decoration: none;
            color: inherit;
        }
        
        .master-data-card {
            min-height: 150px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            border-radius: 0.75rem;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out, background-color 0.2s ease-in-out;
            background-color: #fff;
            border: 1px solid #e9ecef;
            position: relative;
        }

        .master-data-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            background-color: #f8f9fa;
        }
        .master-data-card i { font-size: 3.5rem; margin-bottom: 0.75rem; }
        .master-data-card h5 { font-family: 'Poppins', sans-serif; font-weight: 600; margin-bottom: 0; color: #343a40; }

        /* Styling untuk kartu tambah baru */
        .master-data-card.add-new {
            border: 2px dashed #adb5bd;
            color: #6c757d;
            background-color: #f8f9fa;
        }
        .master-data-card.add-new i { color: #6c757d; }
        .master-data-card.add-new:hover { background-color: #e9ecef; border-color: #6c757d; }

        /* MODAL FIXES */
        .modal-backdrop {
            background-color: rgba(0, 0, 0, 0.5) !important;
        }
        
        .modal {
            z-index: 1055 !important;
        }
        
        .modal-dialog {
            z-index: 1056 !important;
        }
        
        /* Ensure modal content is properly styled */
        .modal-content {
            background-color: #fff !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Fix for any potential overlay issues */
        .modal.show {
            display: block !important;
        }
        
        /* Ensure body doesn't scroll when modal is open */
        body.modal-open {
            overflow: hidden;
            padding-right: 0 !important;
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
                <section class="section">
                    {{-- Alert Peringatan --}}
                    <div class="alert alert-danger fade show mb-4" role="alert" style="background-color: #f8d7da; border-color: #f5c2c7; color: #721c24;">
                        <h4 class="alert-heading mb-1" style="font-family: 'Poppins', sans-serif; font-weight: bold;">Peringatan!</h4>
                        <p class="mb-0" style="font-family: 'Poppins', sans-serif;">Data Dibawah Ini Terhubung Langsung Dengan Aplikasi Pencatatan Barang pada Mobile.</p>
                    </div>

                    {{-- Tombol Hapus Kategori (di luar grid) --}}
                    <button type="button" class="btn btn-danger mb-3" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                        <i class="bi bi-trash"></i> Hapus Kategori
                    </button>

                    <div class="master-data-grid">
                        {{-- Kartu Dinamis dari Database (berdasarkan kategori unik dari master_data) --}}
                        @foreach($cards as $card)
                            <a href="{{ route('supervisor.master.data.specific', $card['category_name']) }}" class="master-data-card-link">
                                <div class="card master-data-card">
                                    <h5>{{ $card['title'] }}</h5>
                                </div>
                            </a>
                        @endforeach

                        {{-- Kartu untuk Tambah Kategori Baru (MEMICU MODAL) --}}
                        <a href="#" class="master-data-card-link" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                            <div class="card master-data-card add-new">
                                <h5>Tambah Kategori Konfigurasi Baru</h5> {{-- Ubah teks --}}
                            </div>
                        </a>
                    </div>
                </section>
            </div>
        </div>
    </div>

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

    <div class="modal fade" id="deleteCategoryModal" tabindex="-1" aria-labelledby="deleteCategoryModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteCategoryForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="deleteCategoryModalLabel">Hapus Kategori Konfigurasi</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Pilih kategori konfigurasi yang ingin Anda hapus. <strong>Perhatian: Semua field di bawah kategori ini akan ikut terhapus!</strong></p>
                        <div class="mb-3">
                            <label for="categoryToDelete" class="form-label">Pilih Kategori Konfigurasi</label>
                            <select class="form-select" id="categoryToDelete" name="category_name" required>
                                <option value="">-- Pilih Kategori Konfigurasi --</option>
                                @foreach($uniqueCategories as $cat)
                                    <option value="{{ $cat }}">{{ ucfirst(str_replace('_', ' ', $cat)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus Kategori Konfigurasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH KATEGORI BARU --}}
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form id="addCategoryForm" action="{{ route('supervisor.master.data.store') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="addCategoryModalLabel">Tambah Kategori Konfigurasi Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="new_category_name" class="form-label">Nama Kategori Konfigurasi Baru</label>
                            <input type="text" class="form-control" id="new_category_name" name="category" placeholder="Contoh: APAR, Hydrant" required>
                            <input type="hidden" name="value" value="default_field_name">
                            <input type="hidden" name="label_display" value="Default Label">
                            <input type="hidden" name="input_type" value="text">
                            <input type="hidden" name="field_order" value="1">
                            <input type="hidden" name="is_required" value="0">
                            <input type="hidden" name="options_category" value="">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize modals properly
            var addCategoryModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
            var deleteCategoryModal = new bootstrap.Modal(document.getElementById('deleteCategoryModal'));
            
            // Ketika form tambah kategori disubmit
            $('#addCategoryForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var method = form.attr('method');
                var formData = form.serialize();

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        addCategoryModal.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Kategori berhasil ditambahkan.',
                            timer: 2500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        addCategoryModal.hide();
                        var errorMessage = 'Terjadi kesalahan. Mohon coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            var errors = xhr.responseJSON.errors;
                            if (errors && errors.category) {
                                errorMessage = errors.category[0];
                            } else if (errors && errors.value) {
                                errorMessage = errors.value[0];
                            } else {
                                errorMessage = xhr.responseJSON.message || 'Terjadi kesalahan tidak dikenal.';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                });
            });

            // Ketika form hapus kategori disubmit
            $('#deleteCategoryForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var categoryName = $('#categoryToDelete').val();
                
                if (!categoryName) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan!',
                        text: 'Pilih kategori yang ingin dihapus terlebih dahulu.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }
                
                var url = "{{ route('supervisor.master.data.destroy_category', ':category_name') }}";
                url = url.replace(':category_name', categoryName);

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: "Anda akan menghapus kategori '" + categoryName + "' dan semua nilai di dalamnya!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: form.serialize(),
                            dataType: 'json',
                            success: function(response) {
                                deleteCategoryModal.hide();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message || 'Kategori berhasil dihapus.',
                                    timer: 2500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                deleteCategoryModal.hide();
                                var errorMessage = 'Terjadi kesalahan. Mohon coba lagi.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: errorMessage,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });
            });

            // Clear form when modal is hidden
            $('#addCategoryModal').on('hidden.bs.modal', function () {
                $('#addCategoryForm')[0].reset();
            });

            $('#deleteCategoryModal').on('hidden.bs.modal', function () {
                $('#deleteCategoryForm')[0].reset();
            });
        });
    </script>
</body>

</html>
@endsection