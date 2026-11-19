@extends('layouts/main')

@section('container')
<div id="main">
    <header class="mb-3">
        <a href="#" class="burger-btn d-block d-xl-none" aria-label="Buka menu">
            <i class="bi bi-justify fs-3"></i>
        </a>
    </header>

    <div class="page-heading">
        <h3>{{ $judul }}</h3>
    </div>

    <div class="page-content">
        @if (session('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">Periksa kembali isian yang wajib dan format datanya.</div>
        @endif

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-3">Jenis Barang</h4>
                    <div class="btn-group" role="group" aria-label="Pilih jenis barang">
                        <button type="button" class="btn btn-primary" data-item-type="Barang Jadi" aria-pressed="true">Barang Jadi</button>
                        <button type="button" class="btn btn-outline-primary" data-item-type="Sparepart" aria-pressed="false">Sparepart</button>
                    </div>
                </div>

                <div class="card-body">
                    <form id="input-item-form" action="{{ route('staff_gudang.input_data.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="tipe_barang" id="tipe_barang" value="{{ old('tipe_barang', 'Barang Jadi') }}">

                        <div class="row g-3" id="category-field">
                            <div class="col-12 col-md-6">
                                <label for="tipe_barang_kategori" class="form-label">Kategori</label>
                                <select name="tipe_barang_kategori" id="tipe_barang_kategori" class="form-select" required>
                                    <option value="APAR" @selected(old('tipe_barang_kategori', 'APAR') === 'APAR')>APAR</option>
                                    <option value="HYDRANT" @selected(old('tipe_barang_kategori') === 'HYDRANT')>HYDRANT</option>
                                </select>
                            </div>
                        </div>

                        <div id="dynamic-item-fields" class="row g-3 mt-1"></div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-1"></i> Ajukan untuk Validasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const configs = @json($formConfigs);
        const oldInput = @json(old());
        const typeInput = document.getElementById('tipe_barang');
        const categoryInput = document.getElementById('tipe_barang_kategori');
        const categoryField = document.getElementById('category-field');
        const fieldsContainer = document.getElementById('dynamic-item-fields');
        const typeButtons = document.querySelectorAll('[data-item-type]');
        const itemForm = document.getElementById('input-item-form');

        function renderFields() {
            const itemType = typeInput.value;
            const category = categoryInput.value;
            const isSparepart = itemType === 'Sparepart';
            const fields = itemType === 'Sparepart'
                ? configs.Sparepart
                : (configs[category] || configs.APAR);

            categoryField.hidden = isSparepart;
            categoryInput.disabled = isSparepart;
            categoryInput.required = !isSparepart;
            fieldsContainer.replaceChildren();

            fields.forEach(function (field) {
                const name = field.value;
                if (name === 'tipe_barang' || name === 'tipe_barang_kategori') return;

                const column = document.createElement('div');
                column.className = 'col-12 col-md-6';

                const label = document.createElement('label');
                label.className = 'form-label';
                label.htmlFor = `field-${name}`;
                label.textContent = field.label_display || name.replaceAll('_', ' ');

                const input = document.createElement('input');
                input.className = 'form-control';
                input.id = `field-${name}`;
                input.name = name;
                input.type = ['text', 'number', 'date'].includes(field.input_type)
                    ? field.input_type
                    : 'text';
                input.required = Boolean(field.is_required);
                input.value = oldInput[name] ?? '';

                if (input.type === 'number') {
                    input.min = name === 'jumlah_barang' ? '1' : '0';
                    input.step = name === 'jumlah_barang' ? '1' : 'any';
                }

                const error = document.createElement('div');
                error.className = 'invalid-feedback';
                error.textContent = 'Isian ini wajib diisi.';
                input.addEventListener('invalid', function () {
                    input.classList.add('is-invalid');
                });
                input.addEventListener('input', function () {
                    input.classList.remove('is-invalid');
                });

                column.append(label, input, error);
                fieldsContainer.append(column);
            });
        }

            itemForm.addEventListener('submit', function (event) {
                if (itemForm.dataset.confirmed === 'true') return;

                event.preventDefault();
                Swal.fire({
                    title: 'Konfirmasi Input Barang',
                    text: 'Data akan dibuat sebagai laporan barang masuk dan menunggu validasi Supervisor. Lanjutkan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, lanjutkan',
                    cancelButtonText: 'Periksa kembali',
                    confirmButtonColor: '#198754',
                    cancelButtonColor: '#6c757d',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        itemForm.dataset.confirmed = 'true';
                        itemForm.requestSubmit();
                    }
                });
            });

        typeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                typeInput.value = button.dataset.itemType;
                typeButtons.forEach(function (item) {
                    const selected = item === button;
                    item.classList.toggle('btn-primary', selected);
                    item.classList.toggle('btn-outline-primary', !selected);
                    item.setAttribute('aria-pressed', selected ? 'true' : 'false');
                });
                renderFields();
            });
        });

        categoryInput.addEventListener('change', renderFields);
        renderFields();
    });
</script>
@endsection