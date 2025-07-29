<?php

namespace App\Http\Controllers\Api;

use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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
        $kondisiBaik = ['baik', 'bagus', 'oke', 'good', 'Baik', 'Bagus', 'Oke', 'Good'];

        // Ambil semua barang
        $barangs = Barang::all();

        // Subquery: ambil laporan terakhir (berdasarkan ID terbesar per barang)
       $latestReports = DB::table('laporan_apk as l1')
        ->select('l1.id_barang', 'l1.kondisi_fisik')
        ->join(DB::raw('
            (SELECT id_barang, MAX(id_laporan_pemeliharaan) as max_id
            FROM laporan_apk
            GROUP BY id_barang) as l2
        '), function($join) {
            $join->on('l1.id_barang', '=', 'l2.id_barang')
                ->on('l1.id_laporan_pemeliharaan', '=', 'l2.max_id');
        });

        // Gabungkan ke data barang
        $dataGabungan = DB::table('barangs as b')
            ->leftJoinSub($latestReports, 'laporan', function($join) {
                $join->on('b.id_barang', '=', 'laporan.id_barang');
            })
            ->select(
                'b.id_barang',
                'b.tipe_barang',
                DB::raw('LOWER(COALESCE(laporan.kondisi_fisik, b.kondisi)) as kondisi_terakhir')
            )
            ->get();

        // Hitung total dari tabel barangs (bukan dari hasil join laporan)
        $total = $barangs->count();

        // Ringkasan umum
        $baik = $dataGabungan->whereIn('kondisi_terakhir', $kondisiBaik)->count();
        $perluCek = $dataGabungan->whereNotIn('kondisi_terakhir', $kondisiBaik)->count();

        // Ringkasan khusus per tipe
        $apar = $dataGabungan->filter(function ($item) {
            return strtolower($item->tipe_barang) === 'apar';
        });

        $hydrant = $dataGabungan->filter(function ($item) {
            return strtolower($item->tipe_barang) === 'hydrant';
        });


        $aparTotal = $barangs->filter(function ($item) {
            return strtolower($item->tipe_barang) === 'apar';
        })->count();

        $hydrantTotal = $barangs->filter(function ($item) {
            return strtolower($item->tipe_barang) === 'hydrant';
        })->count();


        $aparBaik = $apar->whereIn('kondisi_terakhir', $kondisiBaik)->count();
        $aparPerluCek = $apar->whereNotIn('kondisi_terakhir', $kondisiBaik)->count();

        $hydrantBaik = $hydrant->whereIn('kondisi_terakhir', $kondisiBaik)->count();
        $hydrantPerluCek = $hydrant->whereNotIn('kondisi_terakhir', $kondisiBaik)->count();

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
