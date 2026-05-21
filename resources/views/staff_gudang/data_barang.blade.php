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
                                        @forelse ($barangDiterima as $index => $item)
                                            @php
                                                $jumlahQr = $item->qrCodes->count();
                                            @endphp
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
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
                                                    @if ($jumlahQr >= $item->jumlah_barang)
                                                        <button type="button"
                                                                class="btn btn-secondary btn-sm"
                                                                disabled
                                                                style="cursor:not-allowed;">
                                                            <i class="bi bi-check-circle"></i>
                                                            QR Lengkap
                                                        </button>
                                                    @elseif ( $item->tipe_barang_kategori == 'Sparepart' )
                                                        <button type="button"
                                                                class="btn btn-secondary btn-sm"
                                                                disabled
                                                                style="cursor:not-allowed;">
                                                            <i class="bi bi-check-circle"></i>
                                                            Sparepart
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-success btn-sm btn-generate-qr"
                                                                data-id="{{ $item->id_barang }}"
                                                                data-nama="{{ $item->nama_barang }}">
                                                            <i class="bi bi-qr-code"></i>
                                                            Generate QR
                                                        </button>
                                                    @endif
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
    $(document).on('click', '.btn-generate-qr', function () {

        var id = $(this).data('id');
        var nama = $(this).data('nama');

        $.get("{{ route('staff_gudang.preview_qrcode', ':id') }}".replace(':id', id), function(res){

            if(res.status === 'full'){
                Swal.fire({
                    icon: 'warning',
                    title: 'Kapasitas Penuh',
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

                if(result.isConfirmed){

                    $.post("{{ url('staff-gudang/store-qrcode') }}", {
                        _token: '{{ csrf_token() }}',
                        id: id,
                        nomor_identifikasi: res.nomor_identifikasi,
                        image: res.qr_image.split(',')[1]
                    }, function(storeRes){

                        var a = document.createElement('a');
                        a.href = storeRes.url;
                        a.download = storeRes.fileName;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);

                        Swal.fire({
                            icon: 'success',
                            title: 'QR berhasil dibuat',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });

                    });
                }
            });
        });

    });

    </script>


@endsection