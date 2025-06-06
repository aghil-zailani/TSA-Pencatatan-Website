<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul ?? 'Home' }} - PT. Tunas Siak Anugrah</title>
    <link rel="shortcut icon" href="{{ url('logo/tsa1.png') }}" type="image/png"> {{-- Sesuaikan path logo --}}

    {{-- Link ke CSS Bootstrap atau CSS utama Anda --}}
    {{-- Contoh menggunakan CDN Bootstrap untuk kesederhanaan --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
        }
        .navbar-custom {
            background-color: #ffffff; /* Warna navbar bisa disesuaikan */
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .content-area {
            margin-top: 20px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,.05);
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 40px;
            color: #6c757d;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ url('logo/tsa1.png') }}" alt="Logo TSA" height="30" class="d-inline-block align-top">
                PT. Tunas Siak Anugrah
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Halo, {{ $namaUser ?? 'Pengguna' }} ({{ $roleUser ?? 'Role Tidak Diketahui' }})
                        </span>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm">Logout</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container content-area">
        <div class="row">
            <div class="col-12">
                @if(session('loginBerhasil'))
                    <div class="alert alert-success">
                        {{ session('loginBerhasil') }}
                    </div>
                @endif
                 @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif


                <h1>Selamat Datang di Sistem Pencatatan dan Pemantauan APK</h1>
                <p class="lead">Ini adalah halaman utama aplikasi.</p>
                <hr>

                <p>Anda login sebagai: <strong>{{ $namaUser ?? 'Pengguna' }}</strong> dengan peran <strong>{{ $roleUser ?? 'Tidak Diketahui' }}</strong>.</p>

                @if(isset($roleUser))
                    @if($roleUser == 'supervisor')
                        <p>Anda dapat mengakses <a href="{{ route('supervisor.dashboard') }}">Dashboard Supervisor</a> untuk melanjutkan.</p>
                    @elseif($roleUser == 'staff_gudang')
                        <p>Anda dapat mengakses <a href="{{ route('staff_gudang.dashboard') }}">Dashboard Staff Gudang</a> untuk melanjutkan.</p>
                    @else
                        <p>Fitur spesifik untuk peran Anda belum tersedia atau silakan hubungi administrator.</p>
                    @endif
                @endif
                {{-- Tambahkan konten lain yang relevan di sini --}}
            </div>
        </div>
    </div>

    <footer class="footer">
        PT. Tunas Siak Anugrah &copy; {{ date('Y') }}
    </footer>

    {{-- Script Bootstrap JS (jika diperlukan oleh komponen Bootstrap seperti dropdown navbar) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>