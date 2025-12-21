<?php

namespace App\Http\Controllers\Api;

use App\Models\Barang;
use App\Models\QrCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SupervisorUmumBarangController extends Controller
{
    public function scanQr(Request $request)
    {
        try {
            $request->validate([
                'nomor_identifikasi' => 'required|string'
            ]);

            $currentUser = Auth::user();

            if (!$currentUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            $nomorIdentifikasi = trim($request->nomor_identifikasi);

            
            if ($nomorIdentifikasi === '' || strtolower($nomorIdentifikasi) === 'nomor identifikasi:') {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'QR kosong, silakan input barang baru.'
                ], 200);
            }

            
            $qrCode = QrCode::with(['barang'])
                ->whereRaw('LOWER(TRIM(nomor_identifikasi)) = ?', [
                    strtolower($nomorIdentifikasi)
                ])
                ->first();

            
            if (!$qrCode || !$qrCode->barang) {
                return response()->json([
                    'status' => 'not_found',
                    'message' => 'Barang belum diinputkan.'
                ], 200);
            }

            $barang = $qrCode->barang;

            
            if (!$this->validateAccess($barang, $currentUser)) {
                return response()->json([
                    'status' => 'access_denied',
                    'message' => $this->getAccessDeniedMessage($currentUser->role),
                    'error_code' => 'ACCESS_DENIED'
                ], 403);
            }

            return response()->json([
                'status' => 'exists',
                'message' => 'Data barang ditemukan',
                'data' => [
                    'id_barang' => $barang->id_barang,
                    'nama_barang' => $barang->nama_barang,
                    'tipe_barang' => $barang->tipe_barang,
                    'lokasi_barang' => $barang->lokasi_barang,
                    'nomor_identifikasi' => $qrCode->nomor_identifikasi,
                    'created_by_role' => $barang->created_by_role ?? null,
                    'created_by_id' => $barang->created_by_id ?? null,
                    'jumlah_barang' => $barang->jumlah_barang,
                    'kondisi' => $barang->kondisi
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error in scanQr: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }



    public function store(Request $request)
    {
        try {
            $currentUser = Auth::user();

            if (!$currentUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak terautentikasi'
                ], 401);
            }

            $validated = $request->validate([
                'nama_barang' => 'required|string|max:255',
                'jumlah_barang' => 'required|integer|min:1',
                'tipe_barang' => 'required|string',
                'satuan' => 'required|string',
                'kondisi' => 'required|string',
                'nomor_identifikasi' => 'required|string',
                'berat_barang' => 'nullable|numeric',
                'harga_beli' => 'nullable|numeric',
                'harga_jual' => 'nullable|numeric',
                'ukuran_barang' => 'nullable|string',
                'merek_barang' => 'nullable|string',
                'lokasi_barang' => 'nullable|string',
                'qr_code_path' => 'nullable|string'
            ]);

            $createdByRole = $currentUser->role;
            $createdById = $currentUser->id;

            
            $qrCode = QrCode::where('nomor_identifikasi', $validated['nomor_identifikasi'])->first();

            if ($qrCode && $qrCode->id_barang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'QR Code ini sudah terhubung dengan barang lain.'
                ], 400);
            }

            
            $barang = Barang::create([
                'created_by_role' => $createdByRole,
                'created_by_id'   => $createdById,
                'nama_barang'     => $validated['nama_barang'],
                'jumlah_barang'   => $validated['jumlah_barang'],
                'tipe_barang'     => $validated['tipe_barang'],
                'berat_barang'    => $validated['berat_barang'] ?? null,
                'satuan'          => $validated['satuan'],
                'kondisi'         => $validated['kondisi'],
                'harga_beli'      => $validated['harga_beli'] ?? null,
                'harga_jual'      => $validated['harga_jual'] ?? null,
                'ukuran_barang'   => $validated['ukuran_barang'] ?? null,
                'merek_barang'    => $validated['merek_barang'] ?? null,
                'lokasi_barang'   => $validated['lokasi_barang'] ?? null,
            ]);

            
            if ($qrCode) {
                $qrCode->update([
                    'id_barang' => $barang->id_barang,
                    'qr_code_path' => $validated['qr_code_path'] ?? $qrCode->qr_code_path,
                ]);
            } else {
                QrCode::create([
                    'id_barang' => $barang->id_barang,
                    'nomor_identifikasi' => $validated['nomor_identifikasi'],
                    'qr_code_path' => $validated['qr_code_path'] ?? null,
                    'tanggal_pembuatan' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil ditambahkan dan QR code diperbarui.',
                'data' => [
                    'id_barang' => $barang->id_barang,
                    'nomor_identifikasi' => $validated['nomor_identifikasi'],
                    'nama_barang' => $barang->nama_barang
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error dalam store barang: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Validasi hak akses user terhadap barang
     */
    private function validateAccess($barang, $user)
    {
        $userRole = strtolower(trim($user->role));
        $creatorRole = strtolower(trim($barang->created_by_role ?? ''));
        $creatorId = $barang->created_by_id;
        $userId = $user->id;

        
        Log::info('Access Validation', [
            'user_role' => $userRole,
            'creator_role' => $creatorRole,
            'user_id' => $userId,
            'creator_id' => $creatorId
        ]);

        switch ($userRole) {
            case 'supervisor_umum':
                
                return $creatorRole === 'supervisor_umum' && (string)$creatorId === (string)$userId;

            case 'inspektor':
                
                return $creatorRole === 'supervisor_umum';

            case 'staff_gudang':
                
                return $creatorRole === 'staff_gudang';

            default:
                return false;
        }
    }

    private function getAccessDeniedMessage($role)
    {
        $messages = [
            'supervisor_umum' => 'Supervisor hanya dapat mengakses barang yang diinput sendiri',
            'inspektor' => 'Inspektor hanya dapat mengakses barang yang diinput oleh supervisor umum',
            'staff_gudang' => 'Staff gudang hanya dapat mengakses barang yang diinput oleh staff gudang'
        ];

        return $messages[$role] ?? 'Anda tidak memiliki akses';
    }
}
