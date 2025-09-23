<?php

namespace App\Http\Controllers\SupervisorUmum;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaporanAPK;

class SupervisorUmumController extends Controller
{
    public function index()
    {
        $riwayat = LaporanAPK::where('created_by_role', 'inspektor')
            ->orderByDesc('updated_at')
            ->get();

        return view('supervisor_umum.riwayat', [
            'judul' => 'Riwayat Pemeliharaan',
            'riwayat' => $riwayat
        ]);
    }
}


