<?php

namespace App\Http\Controllers\Api;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Barang; 
use App\Models\LaporanAPK;
use Illuminate\Support\Facades\Validator; 

class BarangController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role !== 'inspektor') {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke inventory.'
            ], 403);
        }

        $inventory = DB::table('qr_codes')
            ->join('barangs', 'qr_codes.id_barang', '=', 'barangs.id_barang')
            ->select(
                'qr_codes.id_qr_code',
                'qr_codes.nomor_identifikasi',
                'qr_codes.qr_code_path',
                'qr_codes.tanggal_pembuatan',

                'barangs.id_barang',
                'barangs.nama_barang',
                'barangs.tipe_barang_kategori',
                'barangs.kondisi',
                'barangs.lokasi',
                'barangs.merek_barang',
                'barangs.ukuran_barang',
                'barangs.status'
            )
            ->whereIn('barangs.tipe_barang_kategori', ['APAR', 'HYDRANT'])
            ->orderBy('barangs.tipe_barang_kategori')
            ->orderBy('barangs.nama_barang')
            ->get();

        return response()->json([
            'message' => 'List inventory (QR based)',
            'data' => $inventory
        ], 200);
    }


    public function ringkasan(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $kondisiBaik = ['baik', 'bagus', 'oke', 'good', 'Baik', 'Bagus', 'Oke', 'Good'];

            $total = QrCode::count();
            $baik = QrCode::join('barangs', 'qr_codes.id_barang', '=', 'barangs.id_barang')
                ->whereIn('barangs.kondisi', $kondisiBaik)
                ->count();

            $perluCek = $total - $baik;

            $aparTotal = QrCode::join('barangs', 'qr_codes.id_barang', '=', 'barangs.id_barang')
                ->where('barangs.tipe_barang_kategori', 'apar')
                ->count();

            $aparBaik = QrCode::join('barangs', 'qr_codes.id_barang', '=', 'barangs.id_barang')
                ->where('barangs.tipe_barang_kategori', 'apar')
                ->whereIn('barangs.kondisi', $kondisiBaik)
                ->count();


            $hydrantTotal = QrCode::join('barangs', 'qr_codes.id_barang', '=', 'barangs.id_barang')
                ->where('barangs.tipe_barang_kategori', 'hydrant')
                ->count();

            $hydrantBaik = QrCode::join('barangs', 'qr_codes.id_barang', '=', 'barangs.id_barang')
                ->where('barangs.tipe_barang_kategori', 'hydrant')
                ->whereIn('barangs.kondisi', $kondisiBaik)
                ->count();

            return response()->json([
                'total' => $total,
                'baik' => $baik,
                'perlu_cek' => $perluCek,

                'apar' => [
                    'total' => $aparTotal,
                    'baik' => $aparBaik,
                    'perlu_cek' => $aparTotal - $aparBaik,
                ],

                'hydrant' => [
                    'total' => $hydrantTotal,
                    'baik' => $hydrantBaik,
                    'perlu_cek' => $hydrantTotal - $hydrantBaik,
                ],

                'user_role' => $user->role
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    public function showByQrCode(Request $request, $qrCodeData)
    {
        Log::info("🔍 RAW Scan QR: $qrCodeData");

        // ===== Extract Nomor Identifikasi =====
        $nomorIdentifikasi = null;

        foreach (explode("\n", $qrCodeData) as $line) {
            if (stripos($line, 'nomor identifikasi') !== false) {
                $parts = explode(':', $line);
                $nomorIdentifikasi = trim(end($parts));
                break;
            }
        }

        if (!$nomorIdentifikasi) {
            $nomorIdentifikasi = trim($qrCodeData);
        }

        $nomorIdentifikasi = strtoupper(preg_replace('/\s+/', '', $nomorIdentifikasi));
        Log::info("✅ CLEAN QR ID: $nomorIdentifikasi");

        // ===== Ambil QR + Barang =====
        $qrCode = QrCode::with('barang')
            ->where('nomor_identifikasi', $nomorIdentifikasi)
            ->first();

        if (!$qrCode || !$qrCode->barang) {
            return response()->json([
                'status' => 'not_recognized',
                'message' => 'QR Code atau barang tidak dikenali.'
            ], 404);
        }

        $barang = $qrCode->barang;

        // ===== Validasi kategori =====
        if (!in_array(strtoupper($barang->tipe_barang_kategori), ['APAR', 'HYDRANT'])) {
            return response()->json([
                'status' => 'invalid_type',
                'message' => 'Barang bukan APAR atau HYDRANT.'
            ], 400);
        }

        // ===== Ambil inspeksi terakhir =====
        $laporan = LaporanAPK::where('id_barang', $barang->id_barang)
            ->orderBy('tanggal_inspeksi', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        $inspectionStatus = [
            'is_due' => true,
            'status_text' => 'Siap inspeksi',
            'last_inspection' => null,
            'next_inspection' => null,
        ];

        if ($laporan) {
            $lastDate = \Carbon\Carbon::parse($laporan->tanggal_inspeksi);
            $nextDate = $lastDate->copy()->addMonths(6); // contoh 1 bulan

            $inspectionStatus = [
                'is_due' => now()->gte($nextDate),
                'status_text' => now()->gte($nextDate)
                    ? 'Sudah waktunya inspeksi'
                    : 'Belum waktunya inspeksi',
                'last_inspection' => $lastDate->format('d-m-Y'),
                'next_inspection' => $nextDate->format('d-m-Y'),
            ];
        }

        // ===== Response =====
        return response()->json([
            'status' => 'valid',
            'message' => 'Barang valid',
            'data' => [
                'id_barang' => $barang->id_barang,
                'nama_barang' => $barang->nama_barang,
                'tipe_barang_kategori' => $barang->tipe_barang_kategori,
                'kondisi' => $barang->kondisi,
                'lokasi_barang' => $barang->lokasi_barang ?? null,
                'qr_code' => $qrCode->nomor_identifikasi,
                'inspection_status' => $inspectionStatus,
            ]
        ], 200);
    }



    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jumlah_barang' => 'required|integer|min:0',
            'tipe_barang' => 'required|string', 
            'satuan' => 'required|string',
            'kondisi' => 'required|string',
            'berat_barang' => 'nullable|numeric',
            'merek_barang' => 'nullable|string',
            'ukuran_barang' => 'nullable|string',
        ]);

        $barang = Barang::create($validatedData);

        return response()->json([
            'message' => 'Barang berhasil disimpan!',
            'data' => $barang
        ], 201);
    }

    public function show($id)
    {
        $barang = Barang::with([
            'laporanTerakhir.user:id,username'
        ])->findOrFail($id);

        $laporan = $barang->laporanTerakhir;

        return response()->json([
            'message' => 'Detail barang',
            'data' => $barang
        ]);
    }

    public function detailByQr($qrCode)
    {
        $qr = \DB::table('qr_codes')
            ->where('nomor_identifikasi', $qrCode)
            ->first();

        if (!$qr) {
            return response()->json([
                'message' => 'QR Code tidak ditemukan'
            ], 404);
        }


        $barang = \DB::table('barangs')
            ->where('id_barang', $qr->id_barang)
            ->first();

        if (!$barang) {
            return response()->json([
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        // 3. Ambil laporan terakhir
        $lastInspection = \DB::table('laporan_apk as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.id_user')
            ->where('l.id_barang', $barang->id_barang)
            ->orderByDesc('l.tanggal_inspeksi')
            ->select(
                'l.tanggal_inspeksi',
                'l.kondisi_fisik',
                'l.status',
                'l.foto',
                'l.lokasi_alat',
                'l.tindakan',
                'u.username',
                'l.selang',
                'l.pressure_gauge',
                'l.safety_pin'
            )
            ->first();

        return response()->json([
            'message' => 'Detail barang',
            'data' => [
                'id_barang'   => $barang->id_barang,
                'qr_code'     => $qr->nomor_identifikasi, 
                'nama_barang' => $barang->nama_barang,
                'tipe_barang' => $barang->tipe_barang,
                'tipe_barang_kategori' => $barang->tipe_barang_kategori,
                'kondisi'     => $barang->kondisi,

                'laporan_terakhir' => $lastInspection ? [
                    'tanggal_inspeksi' => $lastInspection->tanggal_inspeksi,
                    'kondisi_fisik'    => $lastInspection->kondisi_fisik,
                    'status'           => $lastInspection->status,
                    'foto'             => $lastInspection->foto,
                    'lokasi_alat'      => $lastInspection->lokasi_alat,
                    'tindakan'         => $lastInspection->tindakan,
                    'username'         => $lastInspection->username,
                    'selang'           => $lastInspection->selang,
                    'pressure_gauge'   => $lastInspection->pressure_gauge,
                    'safety_pin'       => $lastInspection->safety_pin,
                ] : null
            ]
        ]);
    }



}