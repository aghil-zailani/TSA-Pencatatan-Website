<?php

namespace App\Http\Controllers\Supervisor;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Pemeliharaan extends Controller
{
    public function pemeliharaanRiwayat() {
        $dummyRiwayat = [
            (object)[
                'report_id' => 1,
                'nama_laporan' => 'Pemeriksaan APAR Januari',
                'total_items' => 5,
                'status' => 'selesai',
                'created_at' => now()->subDays(30),
                'validated_at' => now()->subDays(28)
            ],
            (object)[
                'report_id' => 2,
                'nama_laporan' => 'Pemeriksaan Hydrant Februari',
                'total_items' => 3,
                'status' => 'selesai',
                'created_at' => now()->subDays(15),
                'validated_at' => now()->subDays(13)
            ],
        ];

        return view('supervisor.pemeliharaan.riwayat', [
            'judul' => 'Riwayat Pemeliharaan',
            'riwayat' => $dummyRiwayat
        ]);
    }


    public function pemeliharaanValidasi() {
        $dummyData = [
            (object)[
                'report_id' => 1,
                'nama_laporan' => 'Pemeriksaan APAR Januari',
                'total_items' => 5,
                'status' => 'memproses',
                'created_at' => now()->subDays(2)
            ],
            (object)[
                'report_id' => 2,
                'nama_laporan' => 'Pemeriksaan Hydrant Februari',
                'total_items' => 3,
                'status' => 'memproses',
                'created_at' => now()->subDays(5)
            ],
        ];

        return view('supervisor.pemeliharaan.validasi', [
            'judul' => 'Validasi Pemeliharaan',
            'pengajuanPending' => $dummyData
        ]);
    }

}

