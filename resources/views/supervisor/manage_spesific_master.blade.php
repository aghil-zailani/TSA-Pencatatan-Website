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
        .btn-primary-custom { background-color: #007bff; border-color: #007bff; color: #fff; font-family: 'Poppins', sans-serif; border-radius: 0.5rem; }
        .btn-primary-custom:hover { background-color: #0056b3; border-color: #0056b3; }
        .btn-danger-custom { background-color: #dc3545; border-color: #dc3545; color: #fff; font-family: 'Poppins', sans-serif; border-radius: 0.5rem; }
        .btn-danger-custom:hover { background-color: #c82333; border-color: #c82333; }
        .btn-warning { font-family: 'Poppins', sans-serif; } /* Override default if needed */
        .form-select, .form-control { font-family: 'Poppins', sans-serif; }
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
            <div class="page-content">
                <section class="section">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('supervisor.master.data') }}">Manajemen Data Input Mobile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $judul }}</li>
                        </ol>
                    </nav>
                    <div class="alert alert-info fade show" role="alert">
                        <h4 class="alert-heading mb-1" style="font-family: 'Poppins', sans-serif; font-weight: bold;"><i class="bi bi-info-circle me-2"></i>Informasi</h4>
                        <p class="mb-0" style="font-family: 'Poppins', sans-serif;">Manajemen nilai untuk kategori: <strong>{{ str_replace('_', ' ', ucfirst($category_name)) }}</strong>.</p>
                        <hr>
                        <p class="mb-0" style="font-family: 'Poppins', sans-serif; font-size: 0.9em;">
                            Data di halaman ini akan muncul sebagai pilihan di aplikasi mobile staff gudang.
                            <br>Jika Anda ingin menambah kategori baru yang berbeda dari yang sudah ada di daftar, Anda bisa menggunakan fitur "Tambah Kategori Baru" di halaman utama Manajemen Data.
                        </p>
                    </div>
                    <div class="card shadow h-md-50">
                        <div class="card-header">
                            <h4 class="card-title">Tambah Kolom Baru untuk Kategori "{{ str_replace('_', ' ', ucfirst($category_name)) }}"</h4> {{-- Menampilkan kategori --}}
                        </div>
                        <div class="card-body">
                            <form action="{{ route('supervisor.master.data.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="category" value="{{ $category_name }}"> {{-- Kategori diset otomatis --}}
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="value" class="form-label">Kolom</label>
                                        <input type="text" class="form-control" id="value" name="value" placeholder="Masukkan kolom baru" required>
                                        @error('value')
                                            <div class="text-danger small">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary-custom">Tambah Data</button>
                            </form>
                        </div>
                    </div>

                    <div class="card shadow h-md-50 mt-4">
                        <div class="card-header">
                            <h4 class="card-title">Daftar Data Master: {{ str_replace('_', ' ', ucfirst($category_name)) }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="masterDataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Kolom</th>
                                            <th>Status Aktif</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 1; @endphp
                                        @forelse ($masterData as $data)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ $data->value }}</td>
                                            <td class="text-center">
                                                @if($data->is_active)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                {{-- Tombol Edit --}}
                                                <button type="button" class="btn btn-warning btn-sm edit-master-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editMasterModal"
                                                    data-id="{{ $data->id }}"
                                                    data-category="{{ $data->category }}"
                                                    data-value="{{ $data->value }}"
                                                    data-is-active="{{ $data->is_active ? '1' : '0' }}">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                {{-- Tombol Hapus --}}
                                                <form action="{{ route('supervisor.master.data.destroy', $data->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data master ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger-custom btn-sm">
                                                        <i class="bi bi-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Belum ada data master untuk kategori ini.</td> {{-- colspan disesuaikan --}}
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

        {{-- SweetAlert dan Modal Edit Master (dari master_data.blade.php sebelumnya) --}}
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
    </div>

    {{-- MODAL EDIT DATA MASTER (tetap sama) --}}
    <div class="modal fade" id="editMasterModal" tabindex="-1" aria-labelledby="editMasterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="updateMasterForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title" id="editMasterModalLabel">Edit Data Master</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_master_id">
                        <div class="mb-3">
                            <label for="edit_master_category" class="form-label">Kategori</label>
                            <input type="text" class="form-control" id="edit_master_category" name="category" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="edit_master_value" class="form-label">Nilai</label>
                            <input type="text" class="form-control" id="edit_master_value" name="value" placeholder="Masukkan nilai baru" required>
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="edit_master_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="edit_master_is_active">Aktif</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary-custom">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script JavaScript (tetap sama) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables untuk daftar data master
            $('#masterDataTable').DataTable({
                "pageLength": 10,
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Semua"]
                ],
                "searching": true,
                "info": true,
                "paging": true
            });

            // Ketika tombol "Edit" data master diklik
            $('.edit-master-btn').on('click', function() {
                var id = $(this).data('id');
                var category = $(this).data('category');
                var value = $(this).data('value');
                var isActive = $(this).data('is-active');

                $('#edit_master_id').val(id);
                $('#edit_master_category').val(category);
                $('#edit_master_value').val(value);
                $('#edit_master_is_active').prop('checked', isActive == '1');

                // Atur action form untuk update
                var updateUrl = "{{ route('supervisor.master.data.update', ':id') }}";
                updateUrl = updateUrl.replace(':id', id);
                $('#updateMasterForm').attr('action', updateUrl);
            });

            // Ketika form update data master disubmit
            $('#updateMasterForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');
                var method = form.find('input[name="_method"]').val() || form.attr('method');
                var formData = form.serialize();

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        var masterModal = bootstrap.Modal.getInstance(document.getElementById('editMasterModal'));
                        if (masterModal) { masterModal.hide(); }
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Data master berhasil diperbarui.',
                            timer: 2500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        var masterModal = bootstrap.Modal.getInstance(document.getElementById('editMasterModal'));
                        if (masterModal) { masterModal.hide(); }
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
            });
        });
    </script>
</body>

</html>
@endsection