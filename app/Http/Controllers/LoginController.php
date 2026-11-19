<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Middleware\EnsureSingleActiveSession;

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
            EnsureSingleActiveSession::remember($request->session()->getId());

            return redirect()->intended(route('dashboard'));
        }

    
        return back()->withInput($request->only('id'))
                    ->with('loginError', 'ID atau Password salah.');
    }

    public function logout(Request $request)
    {
        EnsureSingleActiveSession::forgetIfCurrent($request->session()->getId());
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}