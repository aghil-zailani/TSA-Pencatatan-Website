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
    // di App\Http\Controllers\LoginController.php

    public function login(Request $request)
    {
        // Validasi input dari form. 'login_id' adalah nama input di form Anda.
        $request->validate([
            'login_id' => 'required|string', // Pastikan input name di form adalah 'id'
            'password' => 'required|string',
        ]);

        // Menyiapkan kredensial untuk dicocokkan.
        // Kita memberitahu Auth untuk mencocokkan kolom 'id' di database
        // dengan input 'id' dari form.
        $credentials = [
            'id' => $request->login_id,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            // Logika redirect berdasarkan role
            if ($user->role == 'supervisor') {
                return redirect()->intended(route('supervisor.dashboard'));
            } elseif ($user->role == 'staff_gudang') {
                return redirect()->intended(route('staff_gudang.dashboard'));
            }
            
            // Fallback jika tidak ada role spesifik
            return redirect()->intended(route('home'));
        }

        // Jika gagal, kembali ke halaman login dengan pesan error.
        return back()->withInput($request->only('id'))
                    ->with('loginError', 'ID atau Password salah.');
    }

    /**
     * Menangani proses logout web.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}