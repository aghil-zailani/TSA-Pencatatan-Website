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
        .table thead th { font-weight: 600; background-color: #e9ecef; text-align: center; }
        .table tbody td { vertical-align: middle; }
        .btn-primary-custom { background-color: #007bff; border-color: #007bff; color: #fff; border-radius: 0.5rem; }
        .btn-primary-custom:hover { background-color: #0056b3; color: #fff;}
        .btn-danger-custom { background-color: #dc3545; border-color: #dc3545; color: #fff; border-radius: 0.5rem; }
        .btn-danger-custom:hover { background-color: #c82333; color: #fff;}
        .btn-warning { font-family: 'Poppins', sans-serif; }
        .form-select, .form-control { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body>
<div id="main">
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
                <h4 class="alert-heading mb-1"><i class="bi bi-info-circle me-2"></i>Informasi</h4>
                <p class="mb-0">Manajemen nilai untuk kategori: <strong>{{ str_replace('_', ' ', ucfirst($category_name)) }}</strong>.</p>
                <hr>
                <p class="mb-0" style="font-size: 0.9em;">
                    Data di halaman ini akan muncul sebagai pilihan di aplikasi mobile staff gudang.
                    Jika Anda ingin menambah kategori baru, gunakan fitur "Tambah Kategori Baru" di halaman utama Manajemen Data.
                </p>
            </div>

            {{-- Form Tambah --}}
            <div class="card shadow h-md-50">
                <div class="card-header">
                    <h4 class="card-title">Tambah Kolom Baru</h4>
                </div>
                <div class="card-body">
                    <form id="addMasterDataForm" action="{{ route('supervisor.master.data.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="category" value="{{ $category_name }}">

                        <input type="hidden" name="input_type" value="text"> {{-- Input Type Hidden --}}

                        <div class="mb-3">
                            <label class="form-label">Label Display</label>
                            <input type="text" class="form-control" name="label_display" value="{{ old('label_display') }}" required>
                            @error('label_display')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nilai (Nama Field)</label>
                            <p class="text-muted small">*gunakan format camelCase/snake_case, contoh: `namaBarang` atau `nama_barang`</p>
                            <input type="text" class="form-control" name="value" value="{{ old('value') }}" required>
                            @error('value')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Urutan Field</label>
                            <input type="number" class="form-control" name="field_order" value="{{ old('field_order') }}" min="1" required>
                            @error('field_order')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="is_required" {{ old('is_required') == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_required">Required?</label>
                            @error('is_required')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary-custom">Tambah Data</button>
                    </form>
                </div>
            </div>

            {{-- Tabel --}}
            <div class="card shadow h-md-50 mt-4">
                <div class="card-header">
                    <h4 class="card-title">Daftar Data Master</h4>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover" id="masterDataTable">
                        <thead>
                        <tr class="text-center">
                            <th>No</th>
                            <th>Label</th>
                            <th>Nilai (Field Name)</th>
                            <th>Input Tipe</th>
                            <th>Urutan</th>
                            <th>Status Aktif</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($masterData as $i => $data)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $data->label_display }}</td>
                                <td>{{ $data->value }}</td>
                                <td>{{ ucfirst($data->input_type) }}</td>
                                <td>{{ $data->field_order }}</td>
                                <td class="text-center">
                                    @if($data->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button"
                                            class="btn btn-warning btn-sm edit-master-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editMasterModal"
                                            data-id="{{ $data->id }}"
                                            data-category="{{ $data->category }}"
                                            data-value="{{ $data->value }}"
                                            data-is-active="{{ $data->is_active ? '1' : '0' }}"
                                            data-label-display="{{ $data->label_display }}"
                                            data-input-type="{{ $data->input_type }}"
                                            data-field-order="{{ $data->field_order }}"
                                            data-is-required="{{ $data->is_required ? '1' : '0' }}">
                                        Edit
                                    </button>
                                    <form action="{{ route('supervisor.master.data.destroy', $data->id) }}" method="POST" class="d-inline delete-item-confirm">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-custom btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </section>
    </div>

    {{-- SweetAlert --}}
    @if (session('message'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>Swal.fire({ icon: "success", title: "Success!", text: "{{ session('message') }}", timer: 2500, showConfirmButton: false });</script>
    @endif
    @if (session('error'))
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>Swal.fire({ icon: "error", title: "Error!", text: "{{ session('error') }}", timer: 2500, showConfirmButton: false });</script>
    @endif

    {{-- Modal --}}
    <div class="modal fade" id="editMasterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="updateMasterForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Edit Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_master_id">

                        <div class="mb-3">
                            <label>Kategori</label>
                            <input type="text" class="form-control" name="category" id="edit_master_category" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Label Display</label>
                            <input type="text" class="form-control" name="label_display" id="edit_label_display" required>
                        </div>

                        <div class="mb-3">
                            <label>Nilai</label>
                            <input type="text" class="form-control" name="value" id="edit_master_value" required>
                        </div>

                        <div class="mb-3">
                            <label>Input Tipe</label>
                            <select class="form-select" name="input_type" id="edit_input_type" required>
                                <option value="text">Text</option>
                                <option value="number">Number</option>
                                <option value="dropdown">Dropdown</option>
                                <option value="date">Date</option>
                                <option value="gambar">Gambar (Image Picker)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Urutan Field</label>
                            <input type="number" class="form-control" name="field_order" id="edit_field_order" required>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_required" value="1" id="edit_is_required">
                            <label class="form-check-label" for="edit_is_required">Required?</label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="edit_is_active">
                            <label class="form-check-label" for="edit_is_active">Aktif?</label>
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

    {{-- JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function () {
            $('.edit-master-btn').on('click', function () {
                var data = $(this).data();

                $('#edit_master_id').val(data.id);
                $('#edit_master_category').val(data.category);
                $('#edit_label_display').val(data.labelDisplay);
                $('#edit_master_value').val(data.value);
                $('#edit_input_type').val(data.inputType);
                $('#edit_field_order').val(data.fieldOrder);
                $('#edit_is_required').prop('checked', data.isRequired == 1);
                $('#edit_is_active').prop('checked', data.isActive == 1); 

                var url = "{{ route('supervisor.master.data.update', ':id') }}";
                url = url.replace(':id', data.id);
                $('#updateMasterForm').attr('action', url);
            });

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
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
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

            $('#addMasterDataForm').on('submit', function(e) {
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
                        var addModal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
                        if (addModal) { addModal.hide(); }
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
                        var addModal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal'));
                        if (addModal) { addModal.hide(); }
                        var errorMessage = 'Terjadi kesalahan. Mohon coba lagi.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
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
            
            $('.delete-item-confirm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var message = 'Apakah Anda yakin ingin menghapus data master ini?';

                Swal.fire({
                    title: 'Apakah Anda Yakin?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        var url = form.attr('action');
                        var method = form.find('input[name="_method"]').val() || 'POST';
                        var formData = form.serialize();
                        
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: response.message || 'Data berhasil dihapus.',
                                    timer: 2500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function(xhr) {
                                var errorMessage = 'Terjadi kesalahan. Mohon coba lagi.';
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
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
        });
    </script>
@endsection