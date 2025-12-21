<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $data = [
            'judul' => 'Halaman Utama',
            'namaUser' => $user->name ?? $user->username,
            'roleUser' => $user->role
        ];
        return view('home', $data);
    }
}