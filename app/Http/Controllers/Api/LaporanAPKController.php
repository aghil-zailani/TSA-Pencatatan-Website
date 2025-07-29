<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LaporanAPK;
use App\Models\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Notifikasi;

class LaporanAPKController extends Controller
{
    private function detectTipeBarang($qrCode)
    {
        // Deteksi berdasarkan relasi barang
        if ($qrCode->barang && $qrCode->barang->tipe_barang) {
            $tipe = strtoupper(trim($qrCode->barang->tipe_barang));
            if (strpos($tipe, 'APAR') !== false) return 'APAR';
            if (strpos($tipe, 'HYDRANT') !== false) return 'HYDRANT';
        }

        // Deteksi berdasarkan nomor identifikasi
        $identifier = strtoupper(trim($qrCode->nomor_identifikasi));
        if (strpos($identifier, 'APAR') !== false || strpos($identifier, 'APACO') !== false) return 'APAR';
        if (strpos($identifier, 'HYD') !== false || strpos($identifier, 'HYDRANT') !== false) return 'HYDRANT';

        // Deteksi berdasarkan nama barang
        if ($qrCode->barang && $qrCode->barang->nama_barang) {
            $nama = strtoupper($qrCode->barang->nama_barang);
            if (strpos($nama, 'APAR') !== false) return 'APAR';
            if (strpos($nama, 'HYDRANT') !== false) return 'HYDRANT';
        }

        return $qrCode->barang ? strtoupper($qrCode->barang->tipe_barang) : 'UNKNOWN';
    }

    public function store(Request $request)
    {
        $data = $request->all();
        Log::info("📥 Data diterima: " . json_encode($data));

        // Validasi dasar
        $validator = validator($data, [
            'qr_code_data' => 'required|string',
            'tanggal_inspeksi' => 'required|date',
            'lokasi_alat' => 'required|string|max:255',
            'foto' => 'nullable|file|image|max:5120',
            'kondisi_fisik' => 'required|string|in:Good,Korosif,Bad',
            'tindakan' => 'required|string|in:Isi Ulang,Ganti',
        ]);

        if ($validator->fails()) {
            Log::warning("❌ Validasi dasar gagal: " . json_encode($validator->errors()));
            return response()->json(['message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        // 🔍 Normalisasi dan cari QR Code
        $qrCodeInput = strtolower(trim($data['qr_code_data']));
        Log::info("🔍 Mencari QR Code (setelah trim + lower): $qrCodeInput");

        $qrCode = QrCode::whereRaw('LOWER(TRIM(nomor_identifikasi)) = ?', [$qrCodeInput])->first();

        if (!$qrCode) {
            Log::warning("❌ QR Code tidak ditemukan: $qrCodeInput");
            $all = QrCode::pluck('nomor_identifikasi')->toArray();
            return response()->json([
                'message' => 'QR Code tidak ditemukan di sistem.',
                'debug' => [
                    'searched' => $qrCodeInput,
                    'tersedia' => $all
                ]
            ], 404);
        }

        $qrCode->load('barang');

        if (!$qrCode->barang) {
            return response()->json(['message' => 'Barang terkait QR Code tidak ditemukan.'], 404);
        }

        // Deteksi tipe barang
        $tipeBarang = $this->detectTipeBarang($qrCode);
        Log::info("📦 Barang ditemukan: {$qrCode->nomor_identifikasi}, ID: {$qrCode->id_barang}, Tipe: $tipeBarang");

        // Validasi tambahan jika APAR
        if ($tipeBarang === 'APAR') {
            $aparValidator = validator($data, [
                'selang' => 'required|string|in:Good,Bad,Crack',
                'pressure_gauge' => 'required|string|in:Good,Bad',
                'safety_pin' => 'required|string|in:Good,Crack',
            ]);

            if ($aparValidator->fails()) {
                Log::warning("❌ Validasi APAR gagal: " . json_encode($aparValidator->errors()));
                return response()->json([
                    'message' => 'Validasi APAR gagal',
                    'errors' => $aparValidator->errors(),
                ], 422);
            }
        }

        // Validasi tambahan jika HYDRANT (optional)
        if ($tipeBarang === 'HYDRANT') {
            $hydrantValidator = validator($data, [
                'tekanan_air' => 'sometimes|nullable|string|in:Good,Low,Bad',
                'katup' => 'sometimes|nullable|string|in:Good,Bad,Stuck',
                'selang_hydrant' => 'sometimes|nullable|string|in:Good,Bad,Crack',
            ]);

            if ($hydrantValidator->fails()) {
                Log::warning("❌ Validasi HYDRANT gagal: " . json_encode($hydrantValidator->errors()));
                return response()->json([
                    'message' => 'Validasi HYDRANT gagal',
                    'errors' => $hydrantValidator->errors(),
                ], 422);
            }
        }

        // Autentikasi
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Tidak terautentikasi.'], 401);
        }

        // Simpan laporan
        DB::beginTransaction();
        try {
            $laporanData = [
                'id_qr' => $qrCode->nomor_identifikasi,
                'id_barang' => $qrCode->id_barang,
                'id_user' => $user->id,
                'username' => $user->username,
                'nama_barang' => $qrCode->barang->nama_barang,
                'tipe_barang' => $tipeBarang,
                'tanggal_inspeksi' => $data['tanggal_inspeksi'],
                'lokasi_alat' => $data['lokasi_alat'],
                'kondisi_fisik' => $data['kondisi_fisik'],
                'tindakan' => $data['tindakan'],
                'status' => 'Pending',
            ];

            // ✅ Cek apakah ada file
            if ($request->hasFile('foto')) {
                // Simpan di storage/app/public/foto_laporan/
                $path = $request->file('foto')->store('foto_laporan', 'public');

                // Simpan hanya path relatif untuk diakses di web
                $laporanData['foto'] = $path; // hasil: foto_laporan/nama_file.jpg
            }


            if ($tipeBarang === 'APAR') {
                $laporanData['selang'] = $data['selang'];
                $laporanData['pressure_gauge'] = $data['pressure_gauge'];
                $laporanData['safety_pin'] = $data['safety_pin'];
            }

            if ($tipeBarang === 'HYDRANT') {
                $laporanData['tekanan_air'] = $data['tekanan_air'] ?? null;
                $laporanData['katup'] = $data['katup'] ?? null;
                $laporanData['selang_hydrant'] = $data['selang_hydrant'] ?? null;
            }

            $laporan = LaporanAPK::create($laporanData);
            Log::info("✅ Laporan berhasil disimpan. ID: " . $laporan->id);

            // Buat notifikasi setelah laporan masuk
            Notifikasi::create([
                'barang_id' => $qrCode->id_barang,
                'judul' => 'Laporan Baru Dikirim ✉️',
                'deskripsi' => 'Laporan untuk ' . $qrCode->barang->nama_barang . ' sedang menunggu verifikasi.',
                'tipe' => 'info',
                'tanggal' => now()->toDateString(),
                'baru' => true,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Laporan berhasil disimpan!',
                'data' => $laporan,
                'tipe_terdeteksi' => $tipeBarang,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("❌ Gagal simpan laporan: " . $e->getMessage());
            return response()->json([
                'message' => 'Gagal menyimpan laporan.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
