<?php

namespace App\Http\Controllers\StaffGudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $data = array(
            'judul' => 'Data Barang',
            'barang' => AparModel::all(),
        );
        
        $chart = AparModel::all();
        $rs = keluar::where('type','Racking System')->count('type');
        $fp = keluar::where('type','Fire Protection')->count('type');
        
        return view('home', $data, compact('chart', 'rs', 'fp'));
    }

    public function tampil()
    {
        $data = array(
            'judul' => 'Data Barang',
            'barang' => AparModel::all(),
        );
        return view('dataBarang', $data);
    }


    public function barangKeluar()
    {
        $data = array(
            'judul' => 'Stock Barang',
            'barang' => keluar::all(),
        );
        return view('barangKeluar', $data);
    }

    public function exportExcel()
	{
		return Excel::download(new AparExport, 'data_barang.xlsx');
	}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = array(
            'judul' => 'Stock Barang',
            'barang' => AparModel::all(),
        );
        return view('input', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $barang = AparModel::where('namaBarang', $request->namaBarang)->first();
        
        if($barang){
            return redirect('input')->with('error', 'Barang Berhasil Ditambahkan!');
        }

        $data = AparModel::create([
            'namaBarang' => $request->namaBarang,
            'stokBarang' => $request->stokBarang,
            'type' => $request->type,
            'deskripsi' => $request->deskripsi,
            'berat' => $request->berat,
            'harga' => $request->harga,
        ]);

        if ($data) {
            return redirect('input')->with('message', 'Barang Berhasil Ditambahkan!');
        } 
    }

    /**
     * Display the specified resource.
     */
    public function show(AparModel $aparModel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AparModel $aparModel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AparModel $aparModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AparModel $aparModel, $id)
    {
        $data = AparModel::findOrFail($id);
        $data->delete();
        if($data){
            return redirect('dataBarang')->with('message', 'rawr');
        }else{
            return redirect('dataBarang')->with('error', 'rawr');
        }
    }
}