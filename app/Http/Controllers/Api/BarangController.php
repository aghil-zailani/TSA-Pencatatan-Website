<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang; // Pastikan model Barang sudah diimport
use Illuminate\Support\Facades\Validator; // Untuk validasi manual

class BarangController extends Controller
{
    /**
     * Menyimpan data barang baru (sparepart) ke database.
     */
    // ... di dalam Api/BarangController.php

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