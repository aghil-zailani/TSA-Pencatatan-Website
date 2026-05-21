<?php

namespace App\Http\Controllers\Supervisor;

use App\Models\LaporanAPK;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class Pemeliharaan extends Controller
{
    public function pemeliharaanRiwayat()
    {
        $riwayat = LaporanAPK::whereIn('status', ['Diterima', 'Ditolak'])
            ->where('created_by_role', 'inspektor') 
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
            ->whereIn('id_laporan_pemeliharaan', function ($query) {
                $query->selectRaw('MAX(id_laporan_pemeliharaan)')
                    ->from('laporan_apk')
                    ->groupBy('id_barang');
            })
            ->orderByDesc('created_at')
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
            'catatan_validasi' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf($request->status === 'Ditolak')
            ]
        ]);

        try {
            $laporan = LaporanAPK::where('id_laporan_pemeliharaan', $id)->firstOrFail();
            $laporan->status = $request->status;
            $laporan->catatan_validasi = $request->catatan_validasi;
            $laporan->save();

            \App\Models\Notifikasi::create([
                'barang_id' => $laporan->id_barang,
                'judul' => 'Validasi Laporan',
                'deskripsi' => "Laporan {$laporan->nama_barang} telah divalidasi: {$laporan->status}.",
                'tipe' => $laporan->status == 'Diterima' ? 'success' : 'danger',
                'tanggal' => now()->toDateString(),
                'baru' => true,
            ]);

            return redirect()->route('supervisor.pemeliharaan.validasi')
                ->with('success', 'Laporan berhasil divalidasi dan notifikasi dikirim.');
        } catch (\Exception $e) {
            Log::error("Error dalam submitValidasi: " . $e->getMessage());
            return redirect()->route('pemeliharaan.validasi')
                ->with('error', 'Terjadi kesalahan saat memvalidasi laporan.');
        }
    }

}