<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangApiController extends Controller
{
    public function index()
    {
        return Barang::all(); // Mengambil semua barang
    }

    public function store(Request $request)
    {
        $barang = Barang::create($request->all()); // Membuat barang baru
        return response()->json($barang, 201); // Mengembalikan barang dan kode 201 (Created)
    }

    public function show($id)
    {
        return Barang::findOrFail($id); // Mengambil barang berdasarkan ID
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->update($request->all()); // Memperbarui barang
        return $barang;
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete(); // Menghapus barang
        return response()->json(null, 204); // Mengembalikan kode 204 (No Content)
    }
}