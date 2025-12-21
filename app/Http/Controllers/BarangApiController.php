<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangApiController extends Controller
{
    public function index()
    {
        return Barang::all(); 
    }

    public function store(Request $request)
    {
        $barang = Barang::create($request->all()); 
        return response()->json($barang, 201); 
    }

    public function show($id)
    {
        return Barang::findOrFail($id); 
    }

    public function update(Request $request, $id)
    {
        $barang = Barang::findOrFail($id);
        $barang->update($request->all()); 
        return $barang;
    }

    public function destroy($id)
    {
        $barang = Barang::findOrFail($id);
        $barang->delete(); 
        return response()->json(null, 204); 
    }
}