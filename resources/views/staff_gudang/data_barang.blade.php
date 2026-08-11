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
        .status-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-bottom: 1rem;
        }
        .status-filter-btn {
            border: 1px solid #b983ff;
            background: #fff;
            color: #6f42c1;
            padding: 0.5rem 1rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .status-filter-btn.active,
        .status-filter-btn:hover {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: #fff;
            border-color: #7c3aed;
            box-shadow: 0 0.2rem 0.6rem rgba(124, 58, 237, 0.25);
        }
        .action-stack {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-width: 170px;
        }
        .action-stack .btn {
            width: 100%;
            max-width: 170px;
            white-space: nowrap;
        }
        .qr-list-modal .modal-dialog {
            max-width: 920px;
        }
        .qr-list-table th,
        .qr-list-table td {
            vertical-align: middle;
        }
        .qr-list-table .btn {
            white-space: nowrap;
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
                    <div class="card shadow h-md-50">
                        <div class="card-header">
                            <h4 class="card-title">Daftar Barang Diterima</h4>
                        </div>
                        <div class="card-body">
                            <div class="status-filter-bar">
                                <button type="button" class="status-filter-btn active" data-status="all">Semua</button>
                                <button type="button" class="status-filter-btn" data-status="ready">QR Lengkap</button>
                                <button type="button" class="status-filter-btn" data-status="pending">Belum Lengkap</button>
                                <button type="button" class="status-filter-btn" data-status="sparepart">Sparepart</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="text-center">
                                            <th>No</th>
                                            <th>Nama Barang</th>
                                            <th>Tipe</th>
                                            <th>Kondisi</th>
                                            <th>Jumlah Barang</th>
                                            <th>QR Sudah Dibuat</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($barangDiterima as $item)
                                            @php
                                                $jumlahQr = $item->qrCodes->count();
                                                if ($jumlahQr >= $item->jumlah_barang) {
                                                    $statusQr = 'ready';
                                                } elseif ($item->tipe_barang_kategori == 'Sparepart') {
                                                    $statusQr = 'sparepart';
                                                } else {
                                                    $statusQr = 'pending';
                                                }
                                            @endphp
                                            <tr data-status="{{ $statusQr }}">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $item->nama_barang }}</td>
                                                <td>{{ $item->tipe_barang_kategori }}</td>
                                                <td>{{ $item->kondisi ?? '-' }}</td>
                                                <td>{{ $item->jumlah_barang }}</td>

                                                <td class="text-center">
                                                    @if ($jumlahQr >= $item->jumlah_barang)
                                                        <span class="badge bg-success">
                                                            {{ $jumlahQr }} / {{ $item->jumlah_barang }}
                                                        </span>
                                                    @elseif ( $item->tipe_barang_kategori == 'Sparepart' )
                                                        <span class="badge bg-info text-dark">
                                                            Sparepart
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            {{ $jumlahQr }} / {{ $item->jumlah_barang }}
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>{{ $item->created_at->format('d M Y H:i') }}</td>

                                                <td class="text-center">
                                                    <div class="action-stack">
                                                        @if ( $item->tipe_barang_kategori == 'Sparepart' )
                                                            <button type="button"
                                                                    class="btn btn-secondary btn-sm"
                                                                    disabled
                                                                    style="cursor:not-allowed;">
                                                                <i class="bi bi-check-circle"></i>
                                                                Sparepart
                                                            </button>
                                                        @else
                                                            <button type="button"
                                                                    class="btn btn-success btn-sm"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#qrListModal{{ md5($item->id_barang) }}">
                                                                <i class="bi bi-qr-code"></i>
                                                                Generate QR
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">
                                                    Belum ada barang yang diterima.
                                                </td>
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

        @foreach ($barangDiterima as $modalItem)
            @php
                $qrCodesModal = $modalItem->qrCodes->sortBy('nomor_identifikasi')->values();
                $totalUnitModal = max((int) $modalItem->jumlah_barang, $qrCodesModal->count());
            @endphp
            @if ($modalItem->tipe_barang_kategori != 'Sparepart')
                <div class="modal fade qr-list-modal" id="qrListModal{{ md5($modalItem->id_barang) }}" tabindex="-1" aria-labelledby="qrListModalLabel{{ md5($modalItem->id_barang) }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <div>
                                    <h5 class="modal-title" id="qrListModalLabel{{ md5($modalItem->id_barang) }}">
                                        QR Barang - {{ $modalItem->nama_barang }}
                                    </h5>
                                    <small class="text-muted">ID Barang: {{ $modalItem->id_barang }}</small>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover qr-list-table mb-0">
                                        <thead>
                                            <tr class="text-center">
                                                <th style="width: 80px;">Item</th>
                                                <th>Nomor Identifikasi</th>
                                                <th style="width: 150px;">Status QR</th>
                                                <th style="width: 170px;">Tanggal QR</th>
                                                <th style="width: 170px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if ($totalUnitModal > 0)
                                                @for ($unit = 1; $unit <= $totalUnitModal; $unit++)
                                                    @php
                                                        $qrCode = $qrCodesModal->get($unit - 1);
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $unit }}</td>
                                                        <td>{{ $qrCode->nomor_identifikasi ?? '-' }}</td>
                                                        <td class="text-center">
                                                            @if ($qrCode)
                                                                <span class="badge bg-success">Ada QR</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark">Belum Ada</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            {{ optional($qrCode?->tanggal_pembuatan)->format('d M Y H:i') ?? '-' }}
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($qrCode)
                                                                <button type="button"
                                                                        class="btn btn-outline-warning btn-sm btn-regenerate-qr"
                                                                        data-id="{{ $modalItem->id_barang }}"
                                                                        data-qr-id="{{ $qrCode->id_qr_code }}"
                                                                        data-nama="{{ $modalItem->nama_barang }}">
                                                                    <i class="bi bi-arrow-repeat"></i>
                                                                    Generate Ulang
                                                                </button>
                                                            @else
                                                                <button type="button"
                                                                        class="btn btn-success btn-sm btn-generate-qr"
                                                                        data-id="{{ $modalItem->id_barang }}"
                                                                        data-nama="{{ $modalItem->nama_barang }}">
                                                                    <i class="bi bi-qr-code"></i>
                                                                    Generate
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endfor
                                            @else
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">
                                                        Jumlah barang belum tersedia.
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        {{-- SweetAlert untuk Notifikasi Sukses/Error --}}
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

    </body>

    </html>

    {{-- Script JavaScript --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var statusFilter = 'all';

        $(document).ready(function() {
            var table = $('#dataTable').DataTable({
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'Semua']
                ],
                searching: true,
                info: true,
                paging: true,
                ordering: true,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ baris',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    }
                }
            });

            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                var row = table.row(dataIndex).node();
                var rowStatus = $(row).data('status');

                if (statusFilter === 'all') {
                    return true;
                }

                return rowStatus === statusFilter;
            });

            $('.status-filter-btn').on('click', function () {
                $('.status-filter-btn').removeClass('active');
                $(this).addClass('active');
                statusFilter = $(this).data('status');
                table.draw();
            });
        });

        function handleQrPreview(id, nama, mode, qrId = null) {
            var url = "{{ route('staff_gudang.preview_qrcode', ':id') }}".replace(':id', id);
            var params = {};

            if (mode === 'regenerate') {
                params.mode = 'regenerate';
                if (qrId) params.qr_id = qrId;
            }

            $.get(url, params, function(res){
                if (res.status === 'full') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kapasitas Penuh',
                        text: res.message
                    });
                    return;
                }

                if (res.status === 'empty') {
                    Swal.fire({
                        icon: 'error',
                        title: 'QR tidak ditemukan',
                        text: res.message
                    });
                    return;
                }

                Swal.fire({
                    html: `
                            <div class="qr-card-container">
                                <div class="qr-product-name">${nama}</div>
                                
                                <div class="qr-image-wrapper">
                                    <img src="${res.qr_image}" alt="QR Code" class="qr-img-display">
                                </div>
                                
                                <div style="font-size: 0.85rem; color: #95a5a6;">
                                    ID Barang: <b>${id}</b>
                                </div>
                            </div>
                            `,
                    showCloseButton: true,
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: '<i class="fas fa-download"></i> Simpan QR Code',
                    confirmButtonColor: '#3085d6',
                    cancelButtonText: 'Tutup',
                    cancelButtonColor: '#d33',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    },
                    customClass: {
                        confirmButton: 'btn btn-primary btn-lg swal2-confirm-custom',
                        cancelButton: 'btn btn-danger btn-lg'
                    },
                    buttonsStyling: false 
                }).then((result) => {
                    if (result.isConfirmed) {
                        const qrDownloadUrl = res.qr_image;

                        $.post("{{ url('staff-gudang/store-qrcode') }}", {
                            _token: '{{ csrf_token() }}',
                            id: id,
                            qr_id: res.qr_id || qrId || null,
                            mode: mode,
                            nomor_identifikasi: res.nomor_identifikasi,
                            image: res.qr_image.split(',')[1]
                        }, function(storeRes) {
                            var a = document.createElement('a');
                            a.href = qrDownloadUrl;
                            a.download = storeRes.fileName;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);

                            Swal.fire({
                                icon: 'success',
                                title: mode === 'regenerate' ? 'QR berhasil di-generate ulang' : 'QR berhasil dibuat',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        });
                    }
                });
            });
        }

        $(document).on('click', '.btn-generate-qr', function () {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            handleQrPreview(id, nama, 'create');
        });

        $(document).on('click', '.btn-regenerate-qr', function () {
            var id = $(this).data('id');
            var nama = $(this).data('nama');
            var qrId = $(this).data('qr-id');
            handleQrPreview(id, nama, 'regenerate', qrId);
        });
    </script>


@endsection
