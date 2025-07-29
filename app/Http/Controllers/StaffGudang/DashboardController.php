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
        $barangKeluarCount = Keluar::whereBetween('created_at', [$awalBulan, $akhirBulan])->sum('jumlah_barang');

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
        
        $rs = Barang::where('tipe_barang', 'Sparepart')->count();
        $fp = Barang::where('tipe_barang', 'Barang Jadi')->count();
        $riwayatLoginData = LoginHistory::where('user_id', Auth::id())
                                        ->latest('login_at')
                                        ->take(10)
                                        ->get();
        $dataStokBarang = Barang::select(
            'nama_barang',
            \DB::raw('SUM(jumlah_barang) as stok')
        )
        ->groupBy('nama_barang')
        ->get()
        ->map(function($item) {
            return ['nama' => $item->nama_barang, 'stok' => $item->stok];
        })
        ->toArray();

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
        );
        
        return view('staff_gudang.index', $data, compact('rs', 'fp', 'dataStokBarang'));
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
        
        $totalKeseluruhanBarang = Barang::sum('jumlah_barang');

        $judul = 'Monitoring Stok Barang';

        return view('staff_gudang.stokBarang', compact('barangAggregated','judul','totalKeseluruhanBarang'));
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

    public function generateQrCode($id)
    {
        // 1. Cari data barang di database
        $barang = Barang::findOrFail($id);

        // 2. Buat nama file yang aman dari nama barang
        // PERBAIKAN: Tambahkan fallback jika nama barang kosong untuk mencegah nama file yang aneh
        $namaUntukFile = !empty($barang->nama_barang) ? $barang->nama_barang : 'tanpa-nama';
        $safeNamaBarang = Str::slug($namaUntukFile, '_');
        $timestamp = now()->format('YmdHis');
        $fileName = $timestamp . '_' . $barang->id_barang . '_' . $safeNamaBarang . '.png';
        $path = 'qrcodes/' . $fileName;

        // 3. Siapkan konten yang akan dimasukkan ke dalam QR code
        $qrContentArray = [
            'ID Barang: ' . $barang->id_barang,
            'Nama Barang: ' . ($barang->nama_barang ?? 'N/A'), // PERBAIKAN: Handle jika nama barang null
            'Nomor Identifikasi: ' . 'QR-' . $barang->id_barang,
            'Tanggal Pembuatan: ' . now()->format('d-m-Y H:i:s'),
        ];
        $qrContent = implode("\n", $qrContentArray);

        // 4. Buat direktori jika belum ada
        Storage::disk('public')->makeDirectory('qrcodes');

        // 5. Generate gambar QR code
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->size(300)
            ->margin(10) // Tambahkan sedikit margin
            ->build();

        // 6. Simpan file gambar QR code
        Storage::disk('public')->put($path, $result->getString());

        // 7. Simpan atau perbarui path QR code di database
        QrCodeModel::updateOrCreate(
            ['id_barang' => $id],
            [
                'nomor_identifikasi' => 'QR-' . $id,
                'qr_code_path' => $path,
                'tanggal_pembuatan' => now()
            ]
        );

        // 8. Kembalikan URL publik dari gambar QR code
        $url = asset('storage/' . $path);

        return response()->json([
            'url' => $url,
            'fileName' => $fileName,
            'id' => $barang->id_barang,
            'nama' => $barang->nama_barang,
        ], 200);

    }

    public function formPengajuan()
    {
        // Ambil barang-barang yang kondisinya selain 'Bagus'
        $barangTidakBagus = Barang::where('kondisi', '!=', 'Bagus')
                                  ->orderBy('kondisi', 'desc') // Urutkan berdasarkan kondisi
                                  ->orderBy('nama_barang', 'desc')
                                  ->get();
        $judul = 'Halaman Pengajuan Pemeliharaan';

        return view('staff_gudang.pengajuan', compact('barangTidakBagus', 'judul'));
    }

    // Modifikasi metode kirimLaporan untuk bisa mengirim berbagai jenis laporan
    public function kirimLaporan(Request $request)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'page_accessed' => 'Halaman Pengajuan',
            'feature_used' => 'Kirim Laporan',
            'action' => 'View',
            'description' => 'Staff melakukan kirim laporan.'
        ]);

        DB::beginTransaction();
        try {
            $jenisLaporan = $request->input('jenis_laporan', 'Barang Masuk');
            $itemsToProcess = collect();

            // --- LOGIKA UTAMA YANG DIPERBAIKI ---
            if ($jenisLaporan === 'Laporan Kondisi Barang') {
                // Blok ini untuk menangani laporan barang rusak/expired
                $barangRusak = Barang::where('kondisi', '!=', 'Bagus')
                                     ->whereNull('status') // Hanya ambil yang belum pernah diproses
                                     ->get();

                foreach ($barangRusak as $barangItem) {
                    // Buat entri pengajuan baru untuk setiap barang rusak
                    $pengajuan = PengajuanBarang::create([
                        'nama_barang' => $barangItem->nama_barang ?? 'N/A',
                        'tipe_barang_kategori' => $barangItem->tipe_barang_kategori ?? 'N/A',
                        'tipe_barang' => $barangItem->tipe_barang ?? 'N/A',
                        'jenis_barang' => $barangItem->jenis_barang ?? 'N/A',
                        'jumlah_barang' => $barangItem->jumlah_barang ?? 1,
                        'satuan' => $barangItem->satuan ?? 'N/A',
                        'kondisi_barang' => $barangItem->kondisi ?? 'Rusak',
                        'status' => 'pending', // Status awal
                        'catatan_penolakan' => '-',
                        'nama_laporan' => 'Laporan Kondisi Barang',
                    ]);
                    $itemsToProcess->push($pengajuan);

                    // Tandai barang asli sebagai sedang diproses
                    $barangItem->status = 'pengajuan';
                    $barangItem->save();
                }

            } else { // Ini adalah blok untuk "Barang Masuk"
                // Ambil semua pengajuan barang masuk yang statusnya '-' (menunggu dikelompokkan)
                $itemsToProcess = PengajuanBarang::where('status', '-')
                                                 ->get();
            }

            // Jika tidak ada item sama sekali untuk diproses
            if ($itemsToProcess->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Tidak ada item yang perlu dilaporkan.');
            }

            // Generate satu ID unik untuk laporan ini
            $reportId = 'LBM-' . date('ymd') . '-' . strtoupper(Str::random(4));

            foreach ($itemsToProcess as $item) {
                $item->report_id = $reportId;
                $item->status = 'proses';
                if ($item->tipe_barang_kategori === 'Sparepart') {
                    $item->nama_laporan = 'Laporan Sparepart Masuk';
                } else {
                    $item->nama_laporan = 'Laporan Barang Masuk';
                }
                $item->save();
            }

            DB::commit();
            return redirect()->back()->with('message', count($itemsToProcess) . ' item dari "' . $jenisLaporan . '" berhasil dikirim untuk validasi!');

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error saat mengirim laporan: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return redirect()->back()->with('error', 'Gagal mengirim laporan. Silakan hubungi administrator.');
        }
    }

    public function riwayat()
    {
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(100);
        $laporan = PengajuanBarang::whereIn('status', ['diterima', 'ditolak'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(100);
        $judul = 'Riwayat';

        return view('staff_gudang.riwayat', compact('logs', 'laporan', 'judul'));
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
        $validationRules['jenis_barang'] = 'required|string|max:255';

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
        // 1. Validasi data yang masuk dari Flutter
        $validator = Validator::make($request->all(), [
            // Memastikan 'items' adalah array dan tidak kosong
            'items' => 'required|array|min:1',
            // Memastikan setiap item di dalam array memiliki id_barang dan jumlah_barang
            'items.*.id_barang' => 'required|string|exists:barangs,id_barang',
            'items.*.jumlah_barang' => 'required|integer|min:1',
            // Validasi untuk field dinamis lainnya
            'tujuan' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data tidak valid', 'errors' => $validator->errors()], 422);
        }

        $items = $request->input('items');
        $commonData = $request->except('items');

        // 2. Gunakan transaction untuk memastikan integritas data
        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                $barang = Barang::where('id_barang', $item['id_barang'])->first();

                if (!$barang || $barang->jumlah_barang < $item['jumlah_barang']) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Stok untuk barang ' . ($barang->nama_barang ?? $item['id_barang']) . ' tidak mencukupi.'
                    ], 409);
                }

                $reportId = 'LBM-' . date('ymd') . '-' . strtoupper(Str::random(4));

                Transaksi::create([
                    'report_id' => $reportId,
                    'id_barang' => $item['id_barang'],
                    'jumlah_barang' => $item['jumlah_barang'],
                    'tujuan' => $commonData['tujuan'] ?? null,
                    'keterangan' => $commonData['keterangan'] ?? null,
                    'status' => 'keluar',
                    'catatan_penolakan' => '-',
                ]);
            }

            // Jika semua item berhasil diproses, commit transaksi
            DB::commit();

            return response()->json(['message' => 'Barang keluar berhasil dicatat!'], 201);

        } catch (Throwable $e) {
            // Jika terjadi error lain, batalkan transaksi
            DB::rollBack();
            Log::error('Gagal mencatat barang keluar: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan di server.'], 500);
        }
    }

    public function show(Barang $Barang)
    {
        //
    }

    public function edit(Barang $Barang)
    {
        //
    }

    public function update(Request $request, Barang $Barang)
    {
        //
    }

    public function destroy(Barang $Barang, $id)
    {

    }
}