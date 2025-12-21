<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengajuanBarang;
use Illuminate\Support\Facades\Validator;

class PengajuanBarangController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string',
            'tipe_barang_kategori' => 'required|string',
            'tipe_barang' => 'nullable|string',
            'jumlah_barang' => 'required|integer|min:1',
            'satuan' => 'nullable|string',
            'kondisi_barang' => 'nullable|string',
            'berat' => 'nullable|string',
            'tanggal_kadaluarsa' => 'nullable|string',
            'ukuran_barang' => 'nullable|string',
            'panjang' => 'nullable|string',
            'lebar' => 'nullable|string',
            'tinggi' => 'nullable|string',
            'merek' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $pengajuan = PengajuanBarang::create($request->all());

        return response()->json([
            'message' => 'Pengajuan barang berhasil dikirim untuk validasi.',
            'data' => $pengajuan
        ], 201);
    }
}