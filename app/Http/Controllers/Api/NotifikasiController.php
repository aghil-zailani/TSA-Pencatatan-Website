<?php


namespace App\Http\Controllers\Api;

use App\Models\Barang;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        
        if ($currentUser->role !== 'inspektor') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke fitur notifikasi.'
            ], 403);
        }

        
        $notifikasis = Notifikasi::with('barang')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'judul' => $notif->judul,
                    'deskripsi' => $notif->deskripsi,
                    'tipe' => $notif->tipe,
                    'tanggal' => $notif->tanggal,
                    'baru' => $notif->baru,
                    'barang' => [
                        'id_barang' => $notif->barang->id_barang ?? null,
                        'nama_barang' => $notif->barang->nama_barang ?? 'Tidak Diketahui',
                        'lokasi' => $notif->barang->lokasi_barang ?? 'Tidak Diketahui',
                        'tipe_barang' => $notif->barang->tipe_barang ?? '-',
                    ],
                ];
            });

        return response()->json([
            'message' => 'List notifikasi',
            'data' => $notifikasis
        ], 200);
    }



    public function generateNotifikasi()
    {
        $today = Carbon::now();
        $cutoff60 = $today->copy()->subDays(60); 
        $cutoff50 = $today->copy()->subDays(50); 

        $barangs = DB::table('barangs')
            ->leftJoin('laporan_apk', 'barangs.id_barang', '=', 'laporan_apk.id_barang')
            ->select(
                'barangs.id_barang',
                'barangs.nama_barang',
                DB::raw('MAX(laporan_apk.tanggal_inspeksi) as last_checked')
            )
            ->groupBy('barangs.id_barang', 'barangs.nama_barang')
            ->get();

        foreach ($barangs as $barang) {
            if (is_null($barang->last_checked)) {
                
                Notifikasi::updateOrCreate(
                    ['id_barang' => $barang->id_barang, 'tanggal' => now()->toDateString()],
                    [
                        'judul' => 'Barang Belum Pernah Dicek ❌',
                        'deskripsi' => 'Barang ' . $barang->nama_barang . ' belum pernah dicek sejak didaftarkan.',
                        'tipe' => 'warning',
                        'baru' => true,
                    ]
                );
            } else {
                $lastChecked = Carbon::parse($barang->last_checked);

                if ($lastChecked < $cutoff60) {
                    
                    Notifikasi::updateOrCreate(
                        ['id_barang' => $barang->id_barang, 'tanggal' => now()->toDateString()],
                        [
                            'judul' => 'Barang Belum Dicek ⏰',
                            'deskripsi' => 'Barang ' . $barang->nama_barang . ' belum dicek lebih dari 2 bulan.',
                            'tipe' => 'warning',
                            'baru' => true,
                        ]
                    );
                } elseif ($lastChecked < $cutoff50) {
                    
                    $selisih = $lastChecked->diffInDays($today);
                    Notifikasi::updateOrCreate(
                        ['id_barang' => $barang->id_barang, 'tanggal' => now()->toDateString()],
                        [
                            'judul' => 'Barang Akan Jatuh Tempo 📅',
                            'deskripsi' => 'Barang ' . $barang->nama_barang . ' terakhir dicek ' . $selisih . ' hari lalu.',
                            'tipe' => 'info',
                            'baru' => true,
                        ]
                    );
                }
            }
        }

        return response()->json(['message' => 'Notifikasi berhasil digenerate']);
    }

}
