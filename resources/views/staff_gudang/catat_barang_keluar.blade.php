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
            <div class="alert alert-danger">Periksa kembali ID barang, jumlah, tujuan, dan keterangan.</div>
        @endif

        <section class="section">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Form Barang Keluar</h4>
                </div>
                <div class="card-body">
                    @if ($barang->isEmpty())
                        <div class="alert alert-warning mb-0">Belum ada barang dengan stok tersedia.</div>
                    @else
                        @php
                            $oldItems = old('items', [['id_barang' => '', 'jumlah_barang' => 1]]);
                        @endphp
                        <form id="outgoing-item-form" action="{{ route('staff_gudang.barang_keluar.store') }}" method="POST">
                            @csrf

                            <div id="outgoing-items" class="d-grid gap-3">
                                @foreach ($oldItems as $index => $oldItem)
                                    <div class="outgoing-item row g-3 align-items-end">
                                        <div class="col-12 col-md-6">
                                            <label for="item-id-{{ $index }}" class="form-label">ID Barang</label>
                                            <select name="items[{{ $index }}][id_barang]" id="item-id-{{ $index }}" class="form-select item-select" required>
                                                <option value="">Pilih ID barang</option>
                                                @foreach ($barang as $item)
                                                    <option value="{{ $item->id_barang }}" data-stock="{{ $item->jumlah_barang }}" @selected(($oldItem['id_barang'] ?? '') === $item->id_barang)>
                                                        {{ $item->id_barang }} | {{ $item->nama_barang }} | Stok {{ $item->jumlah_barang }} {{ $item->satuan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-10 col-md-5">
                                            <label for="item-quantity-{{ $index }}" class="form-label">Jumlah Keluar</label>
                                            <input type="number" name="items[{{ $index }}][jumlah_barang]" id="item-quantity-{{ $index }}" class="form-control item-quantity" min="1" value="{{ $oldItem['jumlah_barang'] ?? 1 }}" required>
                                            @error("items.{$index}.jumlah_barang")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="col-2 col-md-1">
                                            <button type="button" class="btn btn-outline-danger remove-item" aria-label="Hapus barang" title="Hapus barang" @disabled(count($oldItems) === 1)>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <template id="outgoing-item-template">
                                <div class="outgoing-item row g-3 align-items-end">
                                    <div class="col-12 col-md-6">
                                        <label for="item-id-__INDEX__" class="form-label">ID Barang</label>
                                        <select name="items[__INDEX__][id_barang]" id="item-id-__INDEX__" class="form-select item-select" required>
                                            <option value="">Pilih ID barang</option>
                                            @foreach ($barang as $item)
                                                <option value="{{ $item->id_barang }}" data-stock="{{ $item->jumlah_barang }}">
                                                    {{ $item->id_barang }} | {{ $item->nama_barang }} | Stok {{ $item->jumlah_barang }} {{ $item->satuan }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-10 col-md-5">
                                        <label for="item-quantity-__INDEX__" class="form-label">Jumlah Keluar</label>
                                        <input type="number" name="items[__INDEX__][jumlah_barang]" id="item-quantity-__INDEX__" class="form-control item-quantity" min="1" value="1" required>
                                    </div>
                                    <div class="col-2 col-md-1">
                                        <button type="button" class="btn btn-outline-danger remove-item" aria-label="Hapus barang" title="Hapus barang">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <button type="button" id="add-outgoing-item" class="btn btn-outline-primary mt-3">
                                <i class="bi bi-plus-lg me-1"></i> Tambah Barang
                            </button>

                            <div class="row g-3 mt-1">
                                <div class="col-12">
                                    <label for="tujuan" class="form-label">Tujuan</label>
                                    <input type="text" name="tujuan" id="tujuan" class="form-control" maxlength="255" value="{{ old('tujuan') }}" required>
                                </div>

                                <div class="col-12">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea name="keterangan" id="keterangan" class="form-control" rows="3" maxlength="2000">{{ old('keterangan') }}</textarea>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> Ajukan Barang Keluar
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('outgoing-item-form');
        if (!form) return;

        const itemsContainer = document.getElementById('outgoing-items');
        const itemTemplate = document.getElementById('outgoing-item-template');
        const addItemButton = document.getElementById('add-outgoing-item');
        let nextIndex = itemsContainer.querySelectorAll('.outgoing-item').length;

        function syncRows() {
            const rows = Array.from(itemsContainer.querySelectorAll('.outgoing-item'));
            const selectedIds = rows
                .map(function (row) { return row.querySelector('.item-select').value; })
                .filter(Boolean);

            rows.forEach(function (row) {
                const select = row.querySelector('.item-select');
                const quantity = row.querySelector('.item-quantity');
                const selectedId = select.value;

                Array.from(select.options).forEach(function (option) {
                    option.disabled = option.value !== '' && option.value !== selectedId && selectedIds.includes(option.value);
                });

                const availableStock = Number(select.selectedOptions[0]?.dataset.stock || 0);
                quantity.max = availableStock ? String(availableStock) : '';
                quantity.setCustomValidity(
                    availableStock && Number(quantity.value) > availableStock
                        ? `Stok tersedia hanya ${availableStock}.`
                        : ''
                );
                row.querySelector('.remove-item').disabled = rows.length === 1;
            });
        }

        addItemButton.addEventListener('click', function () {
            const rowMarkup = itemTemplate.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
            itemsContainer.insertAdjacentHTML('beforeend', rowMarkup);
            syncRows();
        });

        itemsContainer.addEventListener('change', syncRows);
        itemsContainer.addEventListener('input', function (event) {
            if (event.target.matches('.item-quantity')) syncRows();
        });
        itemsContainer.addEventListener('click', function (event) {
            const removeButton = event.target.closest('.remove-item');
            if (!removeButton) return;

            removeButton.closest('.outgoing-item').remove();
            syncRows();
        });
        syncRows();

        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === 'true') return;

            event.preventDefault();
            const itemSummary = Array.from(itemsContainer.querySelectorAll('.outgoing-item'))
                .map(function (row) {
                    const select = row.querySelector('.item-select');
                    const quantity = row.querySelector('.item-quantity').value;
                    return `${quantity} x ${select.selectedOptions[0]?.textContent.trim() || 'barang'}`;
                })
                .join('\n');

            Swal.fire({
                title: 'Konfirmasi Barang Keluar',
                text: `Ajukan barang berikut ke ${document.getElementById('tujuan').value} untuk divalidasi?\n${itemSummary}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, ajukan',
                cancelButtonText: 'Periksa kembali',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = 'true';
                    form.requestSubmit();
                }
            });
        });
    });
</script>
@endsection