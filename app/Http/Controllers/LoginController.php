<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{

    public function showLoginForm()
    {
        $data = ['judul' => 'Login'];
        return view('auth.login', $data);
    }

    public function login(Request $request)
    {
    
        $request->validate([
            'login_id' => 'required|string',
            'password' => 'required|string',
        ]);


        $credentials = [
            'id' => $request->login_id,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->role == 'supervisor') {
                return redirect()->intended(route('supervisor.dashboard'));
            } elseif ($user->role == 'staff_gudang') {
                return redirect()->intended(route('staff_gudang.dashboard'));
            } elseif ($user->role == 'supervisor_umum') {
                return redirect()->intended(route('supervisor_umum.riwayat'));
            }
            
        
            return redirect()->intended(route('home'));
        }

    
        return back()->withInput($request->only('id'))
                    ->with('loginError', 'ID atau Password salah.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}