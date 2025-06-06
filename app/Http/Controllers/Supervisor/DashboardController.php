<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Stok;
use App\Models\Keluar;

class DashboardController extends Controller
{
    public function index()
    {
        $data = array(
            'judul' => 'Dashboard',
            'barang' => Barang::all(),
        );
        
        $chart = Barang::all();
        $rs = Keluar::whereHas('barang', function ($query) {
            $query->where('tipe_barang', 'Racking System');
        })->count();

        $fp = Keluar::whereHas('barang', function ($query) {
            $query->where('tipe_barang', 'Fire Protection');
        })->count();
        
        return view('supervisor.index', $data, compact('chart', 'rs', 'fp'));
    }

    public function tampil()
    {
        $data = array(
            'judul' => 'Data Barang',
            'barang' => Barang::all(),
        );
        return view('dataBarang', $data);
    }


    public function barangKeluar()
    {
        $data = array(
            'judul' => 'Stock Barang',
            'barang' => Keluar::all(),
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
            'barang' => Barang::all(),
        );
        return view('input', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $barang = Barang::where('namaBarang', $request->namaBarang)->first();
        
        if($barang){
            return redirect('input')->with('error', 'Barang Berhasil Ditambahkan!');
        }

        $data = Barang::create([
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
    public function show(Barang $Barang)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barang $Barang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barang $Barang)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barang $Barang, $id)
    {
        $data = Barang::findOrFail($id);
        $data->delete();
        if($data){
            return redirect('dataBarang')->with('message', 'rawr');
        }else{
            return redirect('dataBarang')->with('error', 'rawr');
        }
    }
}