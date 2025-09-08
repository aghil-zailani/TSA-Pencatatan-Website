<?php

namespace App\Http\Controllers\StaffGudang;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Stok;
use App\Models\Keluar;
use App\Models\BarangMasuk;
use App\Models\Pengajuan;
use App\Models\LoginHistory;
use App\Models\MasterData;
use App\Models\FormConfig;
use App\Models\PengajuanBarang;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\ExcelExport;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\QrCode as QrCodeModel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    // Dashboard Staff Gudang
    public function index()
    {
        $awalBulan = Carbon::now()->startOfMonth();
        $akhirBulan = Carbon::now()->endOfMonth();
        $barangMasukCount = Barang::whereBetween('created_at', [$awalBulan, $akhirBulan])->sum('jumlah_barang');
        $barangKeluarCount = Transaksi::where('status', 'diterima') // hanya yang sudah divalidasi supervisor
            ->whereBetween('created_at', [$awalBulan, $akhirBulan])
            ->sum('jumlah_barang');

        $chartData = Barang::select(
                'nama_barang',
                \DB::raw('SUM(jumlah_barang) as stokBarang')
            )
            ->groupBy('nama_barang')
            ->orderBy('nama_barang')
            ->get()
            ->map(function($item) {
                return ['country' => $item->nama_barang, 'value' => (int)$item->stokBarang];
            });
        
        $rs = Barang::where('tipe_barang', 'Sparepart')->sum('jumlah_barang');
        $fp = Barang::where('tipe_barang', 'Barang Jadi')->sum('jumlah_barang');
        $riwayatLoginData = LoginHistory::where('user_id', Auth::id())
                                        ->latest('login_at')
                                        ->take(10)
                                        ->get();
        $dataStokBarang = Barang::select(
                'nama_barang',
                \DB::raw('SUM(jumlah_barang) as stok')
            )
            ->groupBy('nama_barang')
            ->get();

        $lowStockItems = $dataStokBarang->filter(function ($item) {
            return $item->stok < 10;
        });

        if (!session()->has('lowStockShown')) {
            // Ambil data yang sama untuk dikirim ke session flash
            $lowStockItemsForModal = $lowStockItems->take(6);

            // Simpan ke session flash biar cuma sekali muncul
            session()->flash('lowStockItems', $lowStockItemsForModal);
            session()->put('lowStockShown', true); // Tandai sudah pernah muncul
        }

            $laporanKeluarTerbaru = Transaksi::select(
                                        'id_transaksi',
                                        'status',
                                        \DB::raw('1 as total_items_in_report'),
                                        'created_at'
                                    )
                                    ->whereIn('status', ['diterima', 'ditolak', 'proses'])
                                    ->orderBy('created_at', 'desc')
                                    ->take(10)
                                    ->get()
                                    ->map(function ($laporan) {
                                        if ($laporan->status === 'proses') {
                                            $laporan->display_status = 'Proses';
                                            $laporan->badge_class = 'badge bg-warning';
                                        } elseif ($laporan->status === 'diterima') {
                                            $laporan->display_status = 'Diterima';
                                            $laporan->badge_class = 'badge bg-success';
                                        } elseif ($laporan->status === 'ditolak') {
                                            $laporan->display_status = 'Ditolak';
                                            $laporan->badge_class = 'badge bg-danger';
                                        } else {
                                            $laporan->display_status = ucfirst($laporan->status);
                                            $laporan->badge_class = 'badge bg-secondary';
                                        }
                                        $laporan->title = 'Barang Keluar ID: ' . $laporan->id_transaksi;
                                        $laporan->link_type = 'keluar';
                                        return $laporan;
                                    });

            $laporanValidasiTerbaru = PengajuanBarang::select(
                                    'report_id',
                                    'nama_laporan',
                                    'status',
                                    \DB::raw('COUNT(*) as total_items_in_report'),
                                    \DB::raw('MIN(created_at) as created_at')
                                )
                                ->whereIn('status', ['diterima', 'ditolak', 'proses'])
                                ->groupBy('report_id', 'nama_laporan', 'status')
                                ->orderBy('created_at', 'desc')
                                ->take(10)
                                ->get()
                                ->map(function($laporan) {
                                    if ($laporan->status === 'sent_to_supervisor') {
                                        $laporan->display_status = 'Proses';
                                        $laporan->badge_class = 'badge bg-warning';
                                    } elseif ($laporan->status === 'diterima') {
                                        $laporan->display_status = 'Diterima';
                                        $laporan->badge_class = 'badge bg-success';
                                    } elseif ($laporan->status === 'ditolak') {
                                        $laporan->display_status = 'Ditolak';
                                        $laporan->badge_class = 'badge bg-danger';
                                    } else {
                                        $laporan->display_status = ucfirst($laporan->status);
                                        $laporan->badge_class = 'badge bg-secondary';
                                    }
                                    $laporan->title = 'Barang Masuk ID: ' . substr($laporan->report_id, 0, 8) . '...';
                                    $laporan->link_type = 'masuk';
                                    return $laporan;
                                });

            $laporanGabungan = $laporanValidasiTerbaru->merge($laporanKeluarTerbaru)
                                            ->sortByDesc('created_at')
                                            ->take(10); // Maksimal 10 terbaru   

            $data = array(
                'judul' => 'Dashboard',
                'barang' => Barang::all(),
                'barangMasukBulanIni' => $barangMasukCount,
                'barangKeluarBulanIni' => $barangKeluarCount,
                'chart' => $chartData,
                'laporanGabungan' => $laporanGabungan,
                'riwayatLogin' => $riwayatLoginData,
                'lowStockItems' => $lowStockItems,
            );

            $totalKeseluruhanBarang = Barang::sum('jumlah_barang');
            
            return view('staff_gudang.index', $data, compact('rs', 'fp', 'dataStokBarang', 'totalKeseluruhanBarang'));
        }

    // Monitoring Stok Barang (Tampil)
    public function tampil()
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'page_accessed' => 'Form Pengajuan Kondisi Barang',
            'feature_used' => 'Pengajuan Barang',
            'action' => 'View',
            'description' => 'Staff melihat daftar barang dengan kondisi tidak bagus.'
        ]);
        
        $barangAggregated = Barang::select(
                'nama_barang',
                'tipe_barang',
                'berat_barang',
                'tipe_barang_kategori',
                'media',
                \DB::raw('SUM(jumlah_barang) as total_stok'), // Menjumlahkan stok
                \DB::raw('GROUP_CONCAT(DISTINCT tipe_barang) as jenis_barang'), // Mengumpulkan semua tipe barang unik
                \DB::raw('GROUP_CONCAT(DISTINCT satuan) as satuan'), // Mengumpulkan semua berat unik
                \DB::raw('ANY_VALUE(harga_beli) as harga_beli'), // Mengambil salah satu harga beli (asumsi harga sama untuk nama barang yang sama)
                \DB::raw('ANY_VALUE(harga_jual) as harga_jual'), // Mengambil salah satu harga jual
                \DB::raw('ANY_VALUE(id_barang) as id_representatif') // Ambil satu ID sebagai representasi untuk modal
            )
            ->groupBy('nama_barang', 'tipe_barang', 'berat_barang','tipe_barang_kategori', 'media')
            ->orderBy('nama_barang')
            ->orderBy('media')
            ->orderBy('tipe_barang_kategori')
            ->orderBy('tipe_barang')
            ->orderBy('berat_barang')
            ->get();

        // Anda juga bisa memformat jenis_barang_list jika ingin tampilan yang lebih rapi (misal: "APAR, Sparepart")
        $barangAggregated->map(function($item) {
            $item->berat_display_formatted = ($item->berat_barang !== null && $item->berat_barang !== '') ? $item->berat_barang . ' Kg' : 'N/A';
            return $item;
        });

        $lowStockItems = $barangAggregated->filter(function ($item) {
            // Ambil semua item yang total_stok nya kurang dari 10
            return $item->total_stok < 10;
        });
        
        $totalKeseluruhanBarang = Barang::sum('jumlah_barang');

        $judul = 'Monitoring Stok Barang';

        return view('staff_gudang.stokBarang', compact('barangAggregated','judul','totalKeseluruhanBarang','lowStockItems'));
    }

    public function barangDiterima()
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'page_accessed' => 'Data Barang',
            'feature_used' => 'Halaman daftar barang',
            'action' => 'View',
            'description' => 'Staff melihat daftar barang masuk yang diterima.'
        ]);

        // Ambil relasi dengan qrCode
        $barangDiterima = Barang::with('qrCodes')->orderBy('created_at', 'desc')->get();
        $judul = 'Data Barang';

        return view('staff_gudang.data_barang', compact('barangDiterima', 'judul'));
    }

    // BARU: Metode untuk menampilkan halaman Buat Laporan Staff Gudang
    public function buatLaporan()
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'page_accessed' => 'Buat Laporan Barang Masuk',
            'feature_used' => 'Halaman membuat laporan',
            'action' => 'View',
            'description' => 'Staff melihat daftar barang masuk.'
        ]);

        $pengajuanPending = PengajuanBarang::where('status', '-')->orderBy('created_at', 'desc')->get();
        $judul = 'Buat Laporan Barang Masuk';

        return view('staff_gudang.buat_laporan', compact('pengajuanPending', 'judul'));
    }

    public function barangKeluar()
    {
        $data = array(
            'judul' => 'Stock Barang',
            'barang' => Keluar::all(),
        );
        return view('barangKeluar', $data);
    }

    public function create()
    {
        $data = array(
            'judul' => 'Stock Barang',
            'barang' => Barang::all(),
        );
        return view('input', $data);
    }

    public function generateQrCode(Request $request, $id)
    {
        $forceGenerate = $request->input('force_generate', false); // default false
        $barang = Barang::findOrFail($id);

        // Cek QR code sudah ada
        $existingQr = QrCodeModel::where('id_barang', $id)->first();

        if ($existingQr && Storage::disk('public')->exists($existingQr->qr_code_path)) {
            if (!$forceGenerate) {
                // Hanya kirim data tanpa generate ulang
                return response()->json([
                    'status' => 'exists',
                    'message' => 'QR Code untuk barang ini sudah pernah digenerate.',
                    'url' => asset('storage/' . $existingQr->qr_code_path),
                    'fileName' => basename($existingQr->qr_code_path),
                    'id' => $barang->id_barang,
                    'nama' => $barang->nama_barang,
                    'qr_status' => $existingQr->status
                ], 200);
            }
        }

        // Proses generate QR baru
        $namaUntukFile = !empty($barang->nama_barang) ? $barang->nama_barang : 'tanpa-nama';
        $safeNamaBarang = Str::slug($namaUntukFile, '_');
        $timestamp = now()->format('YmdHis');
        $fileName = $timestamp . '_' . $barang->id_barang . '_' . $safeNamaBarang . '.png';
        $path = 'qrcodes/' . $fileName;

        $qrContentArray = [
            'ID Barang: ' . $barang->id_barang,
            'Nama Barang: ' . ($barang->nama_barang ?? 'N/A'),
            'Nomor Identifikasi: ' . 'QR-' . $barang->id_barang,
            'Tanggal Pembuatan: ' . now()->format('d-m-Y H:i:s'),
        ];
        $qrContent = implode("\n", $qrContentArray);

        Storage::disk('public')->makeDirectory('qrcodes');

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->size(300)
            ->margin(10)
            ->build();

        Storage::disk('public')->put($path, $result->getString());

        $qrCode = QrCodeModel::updateOrCreate(
            ['id_barang' => $id],
            [
                'nomor_identifikasi' => 'QR-' . $id,
                'qr_code_path' => $path,
                'tanggal_pembuatan' => now(),
                'status' => 'baru'
            ]
        );

        $url = asset('storage/' . $path);

        return response()->json([
            'status' => 'success',
            'message' => $forceGenerate ? 'QR Code berhasil diperbarui.' : 'QR Code berhasil dibuat.',
            'url' => $url,
            'fileName' => $fileName,
            'id' => $barang->id_barang,
            'nama' => $barang->nama_barang,
            'qr_status' => $qrCode->status
        ], 200);
    }


    public function riwayat(Request $request)
    {
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(100);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $transaksis = Transaksi::where('status', '!=', 'proses')
            ->where('status', '!=', 'keluar')
            ->with('barang');

        $pengajuans = PengajuanBarang::where('status', '!=', 'proses')
            ->with('barang');

        if ($startDate) {
            $transaksis = $transaksis->where('created_at', '>=', $startDate);
            $pengajuans = $pengajuans->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $transaksis = $transaksis->where('created_at', '<=', $endDate);
            $pengajuans = $pengajuans->where('created_at', '<=', $endDate);
        }

        $transaksis = $transaksis->get();
        $pengajuans = $pengajuans->get();

        // Gabungkan lalu urutkan terbaru
        $riwayatGabung = $transaksis->merge($pengajuans)->sortByDesc('created_at');
        $judul = 'Riwayat';

        // FIX: Nama variabel diperbaiki menjadi 'riwayatGabung'
        return view('staff_gudang.riwayat', compact('logs', 'riwayatGabung', 'judul'));
    }

    public function pengajuanBarangs(Request $request)
    {
        // 1️⃣ Ambil kategori
        $category = $request->input('tipe_barang_kategori');
        if (!$category) {
            return response()->json(['message' => 'Tipe barang kategori wajib diisi.'], 422);
        }

        // 2️⃣ Ambil konfigurasi form dinamis
        $formConfigs = FormConfig::where('category', $category)->get();
        if ($formConfigs->isEmpty()) {
            return response()->json(['message' => "Konfigurasi form untuk kategori '{$category}' tidak ditemukan."], 404);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
        
        // Asumsikan role disimpan di kolom 'role' pada model User
        $created_by_role = $user->role;
        $created_by_id = $user->id;

        // 3️⃣ Validasi dinamis
        $validationRules = [];
        foreach ($formConfigs as $config) {
            $rules = [];
            $rules[] = $config->is_required ? 'required' : 'nullable';

            if ($config->input_type === 'number') {
                $rules[] = 'numeric';
            } else {
                $rules[] = 'string';
                $rules[] = 'max:255';
            }

            $validationRules[$config->value] = implode('|', $rules);
        }


        $validationRules['tipe_barang_kategori'] = 'required|string';
        

        $validator = Validator::make($request->all(), $validationRules);
        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }
        $validatedData = $validator->validated();

        // 4️⃣ Pemetaan kolom
        $columnMapping = [
            'kondisi' => 'kondisi_barang',
        ];

        $dataToSave = [];
        $pengajuanModel = new PengajuanBarang();
        foreach ($validatedData as $key => $value) {
            $dbColumn = $columnMapping[$key] ?? $key;
            if (in_array($dbColumn, $pengajuanModel->getFillable())) {
                $dataToSave[$dbColumn] = ($value === null || $value === '') ? '-' : $value;
            }
        }

        // 5️⃣ Cek `nama_barang` → kalau ada pakai ID dari `barangs`
        $namaBarang = $validatedData['nama_barang'] ?? null;
        if (!$namaBarang) {
            return response()->json(['message' => 'Nama barang wajib diisi.'], 422);
        }

        $tipeBarang = $validatedData['tipe_barang_kategori'] ?? null;
        $barang = Barang::where('nama_barang', $namaBarang)
                        ->where('tipe_barang_kategori', $tipeBarang)->first();

        if ($barang) {
            // Pakai ID lama
            $idBarang = $barang->id_barang;
        } else {
            // Generate ID baru
            $prefix = strtoupper(substr($validatedData['tipe_barang_kategori'], 0, 3));
            $product = isset($validatedData['tipe_barang']) ? strtoupper(substr($validatedData['tipe_barang'], 0, 3)) : 'GEN';
            $monthYear = date('my');
            $count = PengajuanBarang::where('report_id', 'LIKE', "{$prefix}{$product}-{$monthYear}-%")->count() + 1;
            $increment = str_pad($count, 3, '0', STR_PAD_LEFT);
            $idBarang = "{$prefix}{$product}-{$monthYear}-{$increment}";
        }

        $dataToSave['id_barang'] = $idBarang;
        $dataToSave['report_id'] = $idBarang;

        $dataToSave['created_by_role'] = $created_by_role;
        $dataToSave['created_by_id'] = $created_by_id;

        // 6️⃣ Data meta
        $dataToSave['nama_laporan'] = 'Laporan Barang Masuk';
        $dataToSave['status'] = '-';
        $dataToSave['catatan_penolakan'] = '-';

        // 7️⃣ Simpan
        try {
            $pengajuan = PengajuanBarang::create($dataToSave);
            return response()->json(['message' => 'Pengajuan barang berhasil!', 'data' => $pengajuan], 201);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Terjadi kesalahan saat menyimpan data.', 'error' => $e->getMessage()], 500);
        }
    }


    public function catatBarangKeluar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.id_barang' => 'required|string|exists:barangs,id_barang',
            'items.*.jumlah_barang' => 'required|integer|min:1',
            'tujuan' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        $items = $request->input('items');
        $commonData = $request->except('items');

        DB::beginTransaction();
        try {
            $reportId = 'PBK-' . date('ymd') . '-' . strtoupper(Str::random(4)); // PBK = Pengajuan Barang Keluar

            foreach ($items as $item) {
                PengajuanBarang::create([
                    'report_id' => $reportId,
                    'id_barang' => $item['id_barang'],
                    'jumlah_barang' => $item['jumlah_barang'],
                    'tujuan' => $commonData['tujuan'] ?? null,
                    'keterangan' => $commonData['keterangan'] ?? null,
                    'status' => 'proses', // default status sebelum supervisor validasi
                    'catatan_penolakan' => '-',
                ]);
            }

            // Buat juga "header" transaksi di tabel Transaksi untuk supervisor validasi
            Transaksi::create([
                'report_id' => $reportId,
                'id_barang' => $item['id_barang'],
                'jumlah_barang' => $item['jumlah_barang'],
                'tujuan' => $commonData['tujuan'] ?? null,
                'keterangan' => $commonData['keterangan'] ?? null,
                'status' => 'keluar',
                'catatan_penolakan' => '-',
            ]);

            DB::commit();
            return response()->json(['message' => 'Pengajuan barang keluar berhasil dikirim ke supervisor!'], 201);

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Gagal membuat pengajuan barang keluar: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server.'], 500);
        }
    }

}