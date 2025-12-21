<?php

namespace App\Http\Controllers\Supervisor;

use App\Models\LaporanAPK;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Pemeliharaan extends Controller
{
    public function pemeliharaanRiwayat()
    {
        $riwayat = LaporanAPK::whereIn('status', ['Diterima', 'Ditolak'])
            ->orderByDesc('updated_at') 
            ->get()
            ->map(function ($item) {
                return (object)[
                    'nama_barang' => $item->nama_barang,
                    'total_items' => 1, 
                    'status' => strtolower($item->status),
                    'created_at' => $item->created_at,
                    'validated_at' => $item->updated_at, 
                ];
            });

        return view('supervisor.pemeliharaan.riwayat', [
            'judul' => 'Riwayat Pemeliharaan',
            'riwayat' => $riwayat
        ]);
    }


    public function pemeliharaanValidasi()
    {
        $pengajuanPending = LaporanAPK::where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('supervisor.pemeliharaan.validasi', [
            'judul' => 'Validasi Pemeliharaan',
            'pengajuanPending' => $pengajuanPending
        ]);
    }

    
    public function getLaporanDetail($id)
    {
        try {
            
            Log::info("Mencari laporan dengan ID: " . $id);

            
            if (!is_numeric($id)) {
                Log::error("ID tidak valid: " . $id);
                return response()->json([
                    'error' => 'ID laporan tidak valid'
                ], 400);
            }

            
            $laporan = LaporanAPK::where('id_laporan_pemeliharaan', $id)->first();

            if (!$laporan) {
                Log::error("Laporan tidak ditemukan dengan ID: " . $id);

                
                $allReports = LaporanAPK::select('id_laporan_pemeliharaan', 'nama_barang')->get();
                Log::info("Data laporan yang tersedia: ", $allReports->toArray());

                return response()->json([
                    'error' => 'Laporan tidak ditemukan',
                    'debug_id' => $id,
                    'available_ids' => $allReports->pluck('id_laporan_pemeliharaan')->toArray()
                ], 404);
            }

            
            $laporan->tanggal_inspeksi_formatted = Carbon::parse($laporan->tanggal_inspeksi)->format('d M Y');

            Log::info("Laporan ditemukan: " . $laporan->nama_barang);

            return response()->json($laporan);

        } catch (\Exception $e) {
            Log::error("Error dalam getLaporanDetail: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());

            return response()->json([
                'error' => 'Terjadi kesalahan sistem',
                'message' => $e->getMessage(),
                'debug_id' => $id
            ], 500);
        }
    }

    public function submitValidasi(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Diterima,Ditolak',
            'catatan_validasi' => 'nullable|string|max:255'
        ]);

        try {
            $laporan = LaporanAPK::where('id_laporan_pemeliharaan', $id)->firstOrFail();
            $laporan->status = $request->status;
            $laporan->catatan_validasi = $request->catatan_validasi;
            $laporan->save();

            return redirect()->route('supervisor.pemeliharaan.validasi')
                ->with('success', 'Laporan berhasil divalidasi.');
        } catch (\Exception $e) {
            Log::error("Error dalam submitValidasi: " . $e->getMessage());
            return redirect()->route('pemeliharaan.validasi')
                ->with('error', 'Terjadi kesalahan saat memvalidasi laporan.');
        }
    }
}
