<?php

namespace App\Http\Controllers\Api;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Models\Barang; // Pastikan model Barang sudah diimport
use Illuminate\Support\Facades\Validator; // Untuk validasi manual

class BarangController extends Controller
{
    // ... di dalam file app/Http/Controllers/Api/BarangController.php

    public function index()
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $barangQuery = Barang::query();

        // Logika filter berdasarkan role, sama seperti di metode ringkasan
        switch ($currentUser->role) {
            case 'supervisor_umum':
                // Supervisor umum dapat melihat semua barang yang dibuat oleh supervisor_umum
                $barangQuery->where('created_by_role', 'supervisor_umum');
                break;
            case 'inspektor':
                // Inspektor hanya dapat melihat barang dari supervisor yang ditugaskan
                $barangQuery->where('created_by_role', 'supervisor_umum');
                if (!empty($currentUser->supervisor_id)) {
                    $barangQuery->where('created_by_id', $currentUser->supervisor_id);
                }
                break;
            case 'staff_gudang':
                // Staff gudang hanya dapat melihat barang yang dibuat oleh dirinya sendiri
                $barangQuery->where('created_by_role', 'staff_gudang')
                            ->where('created_by_id', $currentUser->id);
                break;
            default:
                // Role lainnya tidak memiliki akses
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke data ini.'
                ], 403);
        }

        // Ambil semua barang setelah difilter
        $barangs = $barangQuery->get();

        return response()->json([
            'message' => 'List barang berhasil dimuat',
            'data' => $barangs
        ], 200);
    }

    public function ringkasan(Request $request)
    {
        try {$currentUser = Auth::user();
            if (!$currentUser) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $barangQuery = Barang::query();

            // Filter berdasarkan role
            switch ($currentUser->role) {
                case 'supervisor_umum':
                    $barangQuery->where('created_by_role', 'supervisor_umum');
                    break;
                case 'inspektor':
                    $barangQuery->where('created_by_role', 'supervisor_umum');
                    if (!empty($currentUser->supervisor_id)) {
                        $barangQuery->where('created_by_id', $currentUser->supervisor_id);
                    }
                    break;
                case 'staff_gudang':
                    $barangQuery->where('created_by_role', 'staff_gudang')
                                ->where('created_by_id', $currentUser->id);
                    break;
                default:
                    return response()->json(['total' => 0, 'baik' => 0, 'perlu_cek' => 0]);
            }

            // Hitung total, baik, dan perlu_cek langsung di database
            $kondisiBaik = ['baik', 'bagus', 'oke', 'good', 'Baik', 'Bagus', 'Oke', 'Good'];

            $total = $barangQuery->count();
            $baik = (clone $barangQuery)->whereIn('kondisi', $kondisiBaik)->count();
            $perluCek = $total - $baik;

            $response = [
                'total' => $total,
                'baik' => $baik,
                'perlu_cek' => $perluCek,
                'user_role' => $currentUser->role
            ];

            // Ringkasan per tipe hanya untuk staff_gudang
            if ($currentUser->role === 'staff_gudang') {
                $aparTotal = (clone $barangQuery)->where('tipe_barang', 'APAR')->count();
                $aparBaik = (clone $barangQuery)->where('tipe_barang', 'APAR')->whereIn('kondisi', $kondisiBaik)->count();

                $hydrantTotal = (clone $barangQuery)->where('tipe_barang', 'HYDRANT')->count();
                $hydrantBaik = (clone $barangQuery)->where('tipe_barang', 'HYDRANT')->whereIn('kondisi', $kondisiBaik)->count();

                $response['apar'] = [
                    'total' => $aparTotal,
                    'baik' => $aparBaik,
                    'perlu_cek' => $aparTotal - $aparBaik
                ];

                $response['hydrant'] = [
                    'total' => $hydrantTotal,
                    'baik' => $hydrantBaik,
                    'perlu_cek' => $hydrantTotal - $hydrantBaik
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Internal server error', 'message' => $e->getMessage()], 500);
        }
    }

    public function showByQrCode(Request $request, $qrCodeData)
    {
        // Gunakan eager loading untuk memuat relasi barang
        $qrCode = QrCode::with('barang')->where('nomor_identifikasi', $qrCodeData)->first();

        Log::info("QR code dicari: $qrCodeData");

        if ($qrCode && $qrCode->barang) {
            $barang = $qrCode->barang;

            // Tambahkan data created_by_role dan created_by_id ke respons
            return response()->json([
                'status' => 'exists', // Tambahkan status agar konsisten dengan endpoint lain
                'message' => 'Data barang ditemukan',
                'data' => [
                    'id_barang' => $barang->id_barang,
                    'nama_barang' => $barang->nama_barang,
                    'jenis_barang' => $barang->jenis_barang, // Pastikan field ini ada di model Barang
                    'lokasi_barang' => $barang->lokasi_barang,
                    'qr_code_data' => $qrCodeData,
                    'tipe_barang' => $barang->tipe_barang,
                    'jumlah_barang' => $barang->jumlah_barang,
                    'kondisi' => $barang->kondisi,
                    // Tambahkan data penting ini
                    'created_by_role' => $barang->created_by_role ?? null,
                    'created_by_id' => $barang->created_by_id ?? null,
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'not_found', // Tambahkan status agar konsisten
                'message' => 'QR Code tidak dikenali atau tidak terdaftar.'
            ], 404);
        }
    }

    public function store(Request $request)
    {
        // Validasi bisa dibuat lebih dinamis jika perlu, tapi ini contoh dasarnya
        $validatedData = $request->validate([
            'nama_barang' => 'required|string|max:255',
            'jumlah_barang' => 'required|integer|min:0',
            'tipe_barang' => 'required|string', // Pastikan Flutter mengirim field ini
            'satuan' => 'required|string',
            'kondisi' => 'required|string',
            'berat_barang' => 'nullable|numeric',
            'merek_barang' => 'nullable|string',
            'ukuran_barang' => 'nullable|string',
        ]);

        // Simpan ke database
        $barang = Barang::create($validatedData);

        return response()->json([
            'message' => 'Barang berhasil disimpan!',
            'data' => $barang
        ], 201);
    }
}
