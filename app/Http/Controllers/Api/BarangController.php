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
use Illuminate\Support\Facades\Validator; 

class BarangController extends Controller
{
    

    public function index()
    {
        $currentUser = Auth::user();
        if (!$currentUser) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $barangQuery = Barang::query();

        
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
                
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke data ini.'
                ], 403);
        }

        
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
        
        $qrCode = QrCode::with('barang')->where('nomor_identifikasi', $qrCodeData)->first();

        Log::info("QR code dicari: $qrCodeData");

        if ($qrCode && $qrCode->barang) {
            $barang = $qrCode->barang;

            
            return response()->json([
                'status' => 'exists', 
                'message' => 'Data barang ditemukan',
                'data' => [
                    'id_barang' => $barang->id_barang,
                    'nama_barang' => $barang->nama_barang,
                    'jenis_barang' => $barang->jenis_barang, 
                    'lokasi_barang' => $barang->lokasi_barang,
                    'qr_code_data' => $qrCodeData,
                    'tipe_barang' => $barang->tipe_barang,
                    'jumlah_barang' => $barang->jumlah_barang,
                    'kondisi' => $barang->kondisi,
                    
                    'created_by_role' => $barang->created_by_role ?? null,
                    'created_by_id' => $barang->created_by_id ?? null,
                ]
            ], 200);
        } else {
            return response()->json([
                'status' => 'not_found', 
                'message' => 'QR Code tidak dikenali atau tidak terdaftar.'
            ], 404);
        }
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
}
