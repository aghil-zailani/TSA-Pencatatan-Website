<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    // HAPUS ATAU KOMENTARI BAGIAN __construct() INI JIKA ADA
    /*
    public function __construct()
    {
        $this->middleware('auth'); // Ini bisa jadi sumber masalah jika ada typo atau konflik
    }
    */

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