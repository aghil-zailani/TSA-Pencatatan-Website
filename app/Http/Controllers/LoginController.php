<?php

namespace App\Http\Controllers; // Pastikan ini BUKAN di App\Http\Controllers\Api\Auth

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Jika diperlukan untuk mengambil data user tambahan, tapi Auth::user() sudah cukup

class LoginController extends Controller
{
    /**
     * Menampilkan form login web.
     */
    public function showLoginForm()
    {
        $data = ['judul' => 'Login'];
        return view('auth.login', $data); // Pastikan view ini adalah login.blade.php Anda
    }

    /**
     * Menangani proses login dari form web.
     */
    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string', // Atau 'login_id' sesuai name di form Anda
            'password' => 'required|string',
        ]);

        $loginField = 'id'; // Sesuaikan dengan nama kolom di DB untuk ID Perusahaan Anda
                                  // (misalnya 'username', 'id_perusahaan', atau 'id' jika itu string PK)

        $credentials = [
            $loginField => $request->login_id, // Atau $request->login_id
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Logika redirect berdasarkan role untuk WEB
            if ($user->role == 'supervisor') {
                return redirect()->intended(route('supervisor.dashboard'))
                                 ->with('loginBerhasil', 'Berhasil Login sebagai Supervisor!');
            } elseif ($user->role == 'staff_gudang') {
                return redirect()->intended(route('staff_gudang.dashboard'))
                                 ->with('loginBerhasil', 'Berhasil Login sebagai Staff Gudang!');
            }
            // Default redirect jika peran tidak spesifik
            return redirect()->intended(route('home'))
                             ->with('loginBerhasil', 'Berhasil Login!');
        }

        return back()->withInput($request->only('username')) // Atau $request->only('login_id')
                     ->with('loginError', 'Login Gagal! Periksa kembali ID Perusahaan dan Password Anda.');
    }

    /**
     * Menangani proses logout web.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login')); // Mengarahkan kembali ke halaman login web
    }
}