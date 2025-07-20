<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang; // Pastikan model Barang sudah diimport
use Illuminate\Support\Facades\Validator; // Untuk validasi manual

class BarangController extends Controller
{
    public function index()
    {
        $barangs = Barang::all(); // Ambil semua barang

        return response()->json([
            'message' => 'List semua barang',
            'data' => $barangs
        ], 200);
    }

    public function showByQrCode(Request $request, $qrCodeData)
    {
        // Cari QR Code di database berdasarkan nomor_identifikasi (dari hasil scan)
        $qrCode = QrCode::where('nomor_identifikasi', $qrCodeData)->first();

        if ($qrCode) {
            // Jika QR Code ditemukan, ambil data barang yang berelasi
            $barang = $qrCode->barang; // Menggunakan relasi barang() di model QrCode

            if ($barang) {
                return response()->json([
                    'message' => 'Data barang ditemukan',
                    'data' => [
                        'id_barang' => $barang->id_barang,
                        'nama_barang' => $barang->nama_barang,
                        'jenis_barang' => $barang->jenis_barang, // Asumsi ada di model Barang dan relasi
                        'lokasi_barang' => $barang->lokasi_barang, // Asumsi ada di model Barang dan relasi
                        'qr_code_data' => $qrCodeData, // Kirim kembali data QR mentah
                        // Tambahkan field lain dari barang yang ingin dikirim ke Flutter
                        'tipe_barang' => $barang->tipe_barang,
                        'jumlah_barang' => $barang->jumlah_barang,
                        'kondisi' => $barang->kondisi,
                        // ... dan seterusnya dari model barang
                    ]
                ], 200);
            } else {
                return response()->json(['message' => 'Barang terkait QR Code ini tidak ditemukan.'], 404);
            }
        } else {
            return response()->json(['message' => 'QR Code tidak dikenali atau tidak terdaftar.'], 404);
        }
    }

    public function ringkasan()
    {
        // Ringkasan total alat
        $total = Barang::sum('jumlah_barang');
        $baik = Barang::where('kondisi', 'baik')->sum('jumlah_barang');
        $perluCek = Barang::where('kondisi', 'perlu_cek')->sum('jumlah_barang');

        // Ringkasan APAR
        $aparTotal = Barang::where('tipe_barang', 'apar')->sum('jumlah_barang');
        $aparBaik = Barang::where('tipe_barang', 'apar')->where('kondisi', 'baik')->sum('jumlah_barang');
        $aparPerluCek = Barang::where('tipe_barang', 'apar')->where('kondisi', 'perlu_cek')->sum('jumlah_barang');

        // Ringkasan Hydrant
        $hydrantTotal = Barang::where('tipe_barang', 'hydrant')->sum('jumlah_barang');
        $hydrantBaik = Barang::where('tipe_barang', 'hydrant')->where('kondisi', 'baik')->sum('jumlah_barang');
        $hydrantPerluCek = Barang::where('tipe_barang', 'hydrant')->where('kondisi', 'perlu_cek')->sum('jumlah_barang');

        return response()->json([
            'total' => $total,
            'baik' => $baik,
            'perlu_cek' => $perluCek,
            'apar' => [
                'total' => $aparTotal,
                'baik' => $aparBaik,
                'perlu_cek' => $aparPerluCek
            ],
            'hydrant' => [
                'total' => $hydrantTotal,
                'baik' => $hydrantBaik,
                'perlu_cek' => $hydrantPerluCek
            ]
        ]);
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