<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $judul ?? 'Login' }} - PT. Tunas Siak Anugrah</title>

    {{-- Pastikan path logo ini benar, contoh: public/logo/tsa.png atau public/logo/tsa1.png --}}
    <link rel="shortcut icon" href="{{ url('logo/tsa.png') }}" type="image/png">


    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    {{-- <link rel="stylesheet" href="/dist/assets/css/main/app.css"> --}}

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .login-container {
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logo-company {
            width: 100%;
            min-height: 100px;
            margin-bottom: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 8px;
        }
        .logo-company img {
            max-width: 80%;
            max-height: 120px;
            object-fit: contain;
        }
        .login-form h2 {
            margin-bottom: 25px;
            color: #333;
            font-weight: 600;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .form-control {
            background-color: #f0f2f5;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            text-align: left;
        }
        .form-control::placeholder {
            color: #999;
        }
        .btn-login {
            background-color: #e9ecef;
            color: #495057;
            border: 1px solid #ced4da;
            padding: 12px 15px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn-login:hover {
            background-color: #d3d9df;
            border-color: #adb5bd;
        }
        .alert {
            padding: 10px 15px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
            text-align: left;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-danger ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo-company">
            {{-- Pastikan path logo ini benar dan file ada di folder public/logo/ --}}
            <img src="{{ url('logo/tsa1.png') }}" alt="Logo PT. Tunas Siak Anugrah">
        </div>

        @if(session('loginError'))
            <div class="alert alert-danger">
                {{ session('loginError') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Pastikan route('login') ini mengarah ke controller web login, bukan API --}}
        <form action="{{ route('login') }}" method="POST" class="login-form">
            @csrf
            <div class="form-group">
                {{-- 'name' disesuaikan dengan field yang akan digunakan untuk login --}}
                <input type="text" class="form-control" id="login_id" name="login_id" placeholder="ID Perusahaan" value="{{ old('login_id') }}" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>