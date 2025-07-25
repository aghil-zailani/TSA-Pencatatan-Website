<?php

namespace App\Http\Controllers\Supervisor;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityLog;
use Carbon\Carbon;     
use App\Exports\ExcelExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Throwable;
use App\Models\PengajuanBarang;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function index()
    {
        $awalBulan = Carbon::now()->startOfMonth();
        $akhirBulan = Carbon::now()->endOfMonth();

        // 1. Menghitung jumlah BARANG MASUK bulan ini
        // Asumsi Anda punya tabel/model 'BarangMasuk' yang mencatat setiap kedatangan barang
        // dan menggunakan kolom 'created_at'.
        // Jika tidak ada, Anda bisa menghitung barang baru di tabel 'barangs'
        $barangMasukCount = Barang::whereBetween('created_at', [$awalBulan, $akhirBulan])->sum('jumlah_barang');
        
        // 2. Menghitung jumlah transaksi BARANG KELUAR bulan ini
        // Asumsi Anda punya tabel/model 'Transaksi' (yang sebelumnya Anda sebut 'Keluar')
        $barangKeluarCount = Keluar::whereBetween('created_at', [$awalBulan, $akhirBulan])->sum('jumlah_barang');

        // Data lain untuk chart (jika masih diperlukan)
        $chartData = Barang::select(
                'nama_barang',
                \DB::raw('SUM(jumlah_barang) as stokBarang') // Agregasi total stok per nama_barang
            )
            ->groupBy('nama_barang') // Kelompokkan HANYA berdasarkan nama_barang
            ->orderBy('nama_barang')
            ->get()
            ->map(function($item) {
                // AmCharts membutuhkan format seperti ini: 'country' dan 'value'
                return ['country' => $item->nama_barang, 'value' => (int)$item->stokBarang];
        });
        
        $rs = Barang::where('tipe_barang', 'Sparepart')->count();

        $fp = Barang::where('tipe_barang', 'Barang Jadi')->count();

        $pengajuan = PengajuanBarang::orderBy('created_at', 'desc')->get();

        $riwayatLoginData = LoginHistory::where('user_id', Auth::id())
                                        ->latest('login_at') // Urutkan dari yang terbaru
                                        ->take(10) // Ambil 5 data teratas
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

        // Ini ambil transaksi barang keluar yang statusnya validasi
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
            'pengajuan' => $pengajuan,
            'riwayatLogin' => $riwayatLoginData,
            'laporanGabungan' => $laporanGabungan,
        );
        
        return view('supervisor.index', $data, compact('rs', 'fp', 'dataStokBarang'));
    }

    public function tampil(Request $request)
    {
         // Data barang sama seperti biasa
        $barangAggregated = Barang::select(
                'nama_barang',
                'tipe_barang',
                'berat_barang',
                DB::raw('SUM(jumlah_barang) as total_stok'),
                DB::raw('AVG(harga_beli) as harga_beli'),
                DB::raw('AVG(harga_jual) as harga_jual')
            )
            ->groupBy('nama_barang', 'tipe_barang', 'berat_barang')
            ->get();

        $totalKeseluruhanBarang = $barangAggregated->sum('total_stok');

        // Ambil kolom terpilih dari query
        $selectedColumns = $request->input('columns', [
            'nama_barang',
            'tipe_barang',
            'total_stok',
            'berat_barang',
            'harga_beli',
            'harga_jual',
        ]);

        $judul = "Monitoring Stok Barang";

        return view('supervisor.stokBarang', [
            'barangAggregated' => $barangAggregated,
            'totalKeseluruhanBarang' => $totalKeseluruhanBarang,
            'selectedColumns' => $selectedColumns,
            'judul' => $judul,
        ]);
    }

    public function getMasterDataByCategory($category_name)
    {
        try {
            if ($category_name === 'category_name') {
                // Khusus endpoint untuk ambil LIST KATEGORI
                $categories = MasterData::select('category')->distinct()->pluck('category');
                return response()->json(['categories' => $categories]);
            }

            $data = MasterData::where('category', $category_name)
                            ->where('is_active', true)
                            ->pluck('value');

            return response()->json($data);
        } catch (\Throwable $e) {
            \Log::error("Error fetching master data for category $category_name: " . $e->getMessage(), [
                'exception' => $e, 'request_url' => request()->fullUrl()
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil master data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function updateHarga(Request $request){
        $request->validate([
            'nama_barang' => 'required|string',
            'tipe_barang' => 'required|string',   // TAMBAHAN: Validasi tipe_barang
            'berat_barang' => 'nullable|string', // TAMBAHAN: Validasi berat_barang (string karena bisa 'N/A')
            'harga_beli' => 'nullable|numeric',
        ]);

        $namaBarang = $request->input('nama_barang');
        $tipeBarang = $request->input('tipe_barang');   // AMBIL: tipe_barang
        $beratBarang = $request->input('berat_barang'); // AMBIL: berat_barang
        $hargaBeli = $request->input('harga_beli');

        // PERUBAHAN KRITIS DI SINI: Tambahkan kondisi where untuk tipe_barang dan berat_barang
        $query = Barang::where('nama_barang', $namaBarang);

        // Tambahkan kondisi untuk tipe_barang
        if (!empty($tipeBarang)) {
            $query->where('tipe_barang', $tipeBarang);
        }

        // Tambahkan kondisi untuk berat_barang
        // Perhatikan penanganan nilai 'N/A' atau kosong
        if (!empty($beratBarang) && $beratBarang !== 'N/A') {
            $query->where('berat_barang', $beratBarang);
        } else {
            // Jika berat_barang adalah 'N/A' atau kosong, cari yang memang NULL atau kosong di DB
            $query->where(function($q) {
                $q->whereNull('berat_barang')->orWhere('berat_barang', '');
            });
        }

        $updatedCount = $query->update([
            'harga_beli' => $hargaBeli
        ]);

        if ($updatedCount > 0) {
            return response()->json(['success' => true, 'message' => 'Harga barang ' . $namaBarang . ' (Tipe: ' . $tipeBarang . ', Berat: ' . ($beratBarang ?: 'N/A') . ') berhasil diperbarui.'], 200);
        } else {
            return response()->json(['success' => false, 'message' => 'Tidak ada barang ' . $namaBarang . ' (Tipe: ' . $tipeBarang . ', Berat: ' . ($beratBarang ?: 'N/A') . ') yang ditemukan atau diperbarui.'], 404);
        }
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
        // Jika Anda ingin mengekspor data yang sama persis dengan tabel Monitoring Stok Barang:
        // Anda perlu mengambil data dengan query agregasi yang sama
        $barangToExport = Barang::select(
            'nama_barang',
            'tipe_barang',
            'berat_barang',
            \DB::raw('SUM(jumlah_barang) as total_stok'),
            \DB::raw('ANY_VALUE(satuan) as satuan_unit'),
            \DB::raw('ANY_VALUE(harga_beli) as harga_beli'),
            \DB::raw('ANY_VALUE(harga_jual) as harga_jual')
        )
        ->groupBy('nama_barang', 'tipe_barang', 'berat_barang')
        ->orderBy('nama_barang')
        ->orderBy('tipe_barang')
        ->orderBy('berat_barang')
        ->get();

        // Format data agar sesuai dengan headings di ExcelExport (dan tambahkan formatting berat)
        $formattedData = $barangToExport->map(function($item) {
            $berat = $item->berat_barang;
            $satuan = $item->satuan_unit;
            $berat_display_formatted = ($berat !== null && $berat !== '') ? number_format((float)$berat, 2, '.', '') . ' ' . ($satuan ?? '') : 'N/A'; // Hilangkan 'N/A' ganda
            $hargaBeliFormatted = $item->harga_beli ?? 'N/A';
            $hargaJualFormatted = $item->harga_jual ?? 'N/A';

            return [
                'nama_barang' => $item->nama_barang, // Cocokkan dengan headings
                'tipe_barang' => $item->tipe_barang,
                'total_stok' => $item->total_stok,
                'berat_display_formatted' => $berat_display_formatted,
                'harga_beli' => $hargaBeliFormatted,
                'harga_jual' => $hargaJualFormatted,
            ];
        })->toArray();

        // Menggunakan nama kelas export Anda: new ExcelExport($formattedData)
        // Gunakan format .xlsx
        return Excel::download(new ExcelExport($formattedData), 'data_barang.xlsx');
    }

    public function exportPdf()
    {
        // Ambil data yang sama dengan tabel
        $barangAggregated = Barang::select(
                'nama_barang',
                'tipe_barang',
                'berat_barang as berat_barang',
                \DB::raw('SUM(jumlah_barang) as total_stok'),
                \DB::raw('AVG(harga_beli) as harga_beli'),
                \DB::raw('AVG(harga_jual) as harga_jual')
            )
            ->groupBy('nama_barang', 'tipe_barang', 'berat_barang')
            ->get();

        $totalKeseluruhanBarang = $barangAggregated->sum('total_stok');

        // Load view untuk PDF (buat file view baru!)
        $pdf = Pdf::loadView('supervisor.pdf.monitoring_stok', [
            'barangAggregated' => $barangAggregated,
            'totalKeseluruhanBarang' => $totalKeseluruhanBarang
        ]);
        

        return $pdf->download('monitoring_stok_barang.pdf');
        return $pdf->stream('monitoring_stok_barang.pdf');
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

    // Metode utama untuk halaman indeks "Manajemen Data" (menampilkan kartu pilihan berdasarkan category dari master_data)
    public function manageMasterData()
    {
        // === BARU: Ambil semua kategori unik dari database untuk kartu dinamis ===
        // Pastikan ini mengambil data dari tabel master_data, bukan Barang
        $uniqueCategories = MasterData::distinct()->pluck('category');

        $displayCategoryMap = [
            'tipe_apar' => ['title' => 'APAR (Tipe)', 'icon' => 'bi bi-fire', 'color_class' => 'icon-danger'],
            'tipe_hydrant' => ['title' => 'Hydrant (Tipe)', 'icon' => 'bi bi-water', 'color_class' => 'icon-primary'],
            'merek' => ['title' => 'Merek', 'icon' => 'bi bi-tags-fill', 'color_class' => 'icon-success'],
            'satuan' => ['title' => 'Satuan', 'icon' => 'bi bi-rulers', 'color_class' => 'icon-info'],
            'kondisi' => ['title' => 'Kondisi', 'icon' => 'bi bi-heart-pulse', 'color_class' => 'icon-warning'],
            'tipe_barang_kategori' => ['title' => 'Kategori Barang', 'icon' => 'bi bi-list-check', 'color_class' => 'icon-secondary'],
            'tujuan_keluar' => ['title' => 'Tujuan Keluar', 'icon' => 'bi bi-send-fill', 'color_class' => 'icon-primary'],
            'jenis_transaksi_keluar' => ['title' => 'Jenis Transaksi Keluar', 'icon' => 'bi bi-cash-stack', 'color_class' => 'icon-success'],
        ];

        $cards = [];
        foreach ($uniqueCategories as $category) {
            $cards[] = [
                'category_name' => $category,
                'title' => $displayCategoryMap[$category]['title'] ?? ucfirst(str_replace('_', ' ', $category)),
                'icon' => $displayCategoryMap[$category]['icon'] ?? 'bi bi-question-circle',
                'color_class' => $displayCategoryMap[$category]['color_class'] ?? 'icon-secondary'
            ];
        }

        $judul = 'Manajemen Data Input Mobile';
        // PASTIKAN 'uniqueCategories' ada di compact()
        return view('supervisor.master_data', compact('judul', 'cards', 'uniqueCategories'));
    }

    // Parameter diubah dari $tipe_barang_name menjadi $category_name
    public function manageSpecificMaster($form_config_category) // Ubah parameter menjadi $form_config_category
    {
        // Ambil konfigurasi field untuk form_type ini
        $masterData = MasterData::where('category', $form_config_category)
                                ->orderBy('field_order') // Urutkan berdasarkan field_order
                                ->get();
        
        // Ambil semua nilai unik dari kolom 'value' di seluruh tabel master_data untuk dropdown di form ini
        $allUniqueValues = MasterData::distinct()->pluck('value');

        // Tentukan judul tampilan
        $displayTitleMap = [
            'form_config_apar_sparepart' => 'Konfigurasi Form: APAR Sparepart',
            'form_config_sparepart' => 'Konfigurasi Form: Sparepart',
            'form_config_barang_jadi' => 'Konfigurasi Form: Barang Jadi',
            'form_config_hydrant_barang_jadi' => 'Konfigurasi Form: Hydrant Barang Jadi', // Tambahkan jika ada
            'form_config_barang_keluar' => 'Konfigurasi Form: Barang Keluar', // Tambahkan jika ada
        ];
        $judul = $displayTitleMap[$form_config_category] ?? 'Konfigurasi Form: ' . ucfirst(str_replace('_', ' ', str_replace('form_config_', '', $form_config_category)));
        
        return view('supervisor.manage_spesific_master', [
        'masterData' => $masterData,
        'judul' => $judul,
        'category_name' => $form_config_category, // Ini sekarang benar di dalam array
        'allUniqueValues' => $allUniqueValues
    ]);
    }

    // Metode untuk menyimpan data master (Store) - TETAP SAMA
    public function storeMasterData(Request $request)
    {
        // Menyesuaikan aturan validasi
        $request->validate([
            'category' => 'required|string|max:50',
            'value' => 'required|string|max:255',
            'label_display' => 'required|string|max:255',
            'field_order' => 'required|integer',
            'is_required' => 'nullable|boolean', // Gunakan nullable|boolean untuk checkbox
        ]);

        // Cek duplikasi data
        $existing = MasterData::where('category', $request->category)
                              ->where('value', $request->value)
                              ->first();
        if ($existing) {
            return redirect()->back()
                ->with('error', 'Field "' . $request->value . '" sudah ada untuk kategori "' . $request->category . '".')
                ->withInput(); // Mengembalikan input sebelumnya
        }

        // Logika Create yang Disesuaikan
        MasterData::create([
            'category' => $request->category,
            'value' => $request->value,
            'label_display' => $request->label_display,
            'input_type' => 'text', // Diisi manual sesuai logika Anda sebelumnya
            'field_order' => $request->field_order,
            // $request->has('is_required') akan mengembalikan true jika checkbox dicentang, false jika tidak.
            'is_required' => $request->has('is_required'),
            'is_active' => true,
        ]);

        return redirect()->back()->with('message', 'Konfigurasi field berhasil ditambahkan!');
    }

    // Metode untuk update data master (Update) - Akan mengupdate konfigurasi field
    public function updateMasterData(Request $request, MasterData $masterData)
    {
        $request->validate([
            // Validasi unik yang kompleks ini sudah benar.
            'value' => 'required|string|max:255|unique:master_data,value,' . $masterData->id . ',id,category,' . $masterData->category,
            'label_display' => 'required|string|max:255',
            'input_type' => 'required|string|in:text,number,dropdown,date',
            'field_order' => 'required|integer',
            'is_active' => 'nullable|boolean', // PERBAIKAN: Menggunakan nullable|boolean
        ]);

        $masterData->value = $request->input('value');
        $masterData->label_display = $request->input('label_display');
        $masterData->input_type = $request->input('input_type');
        $masterData->field_order = $request->input('field_order');
        $masterData->is_required = $request->has('is_required');
        $masterData->is_active = $request->has('is_active'); // PERBAIKAN: Menggunakan $request->has() untuk checkbox
        $masterData->save();

        return redirect()->back()->with('message', 'Konfigurasi field berhasil diperbarui!');
    }

    // Metode untuk menghapus nilai spesifik dari master_data (tetap sama)
    public function destroyMasterData(MasterData $masterData)
    {
        $masterData->delete();
        return redirect()->back()->with('message', 'Konfigurasi field berhasil dihapus!');
    }

    // Metode untuk menghapus SELURUH KATEGORI (misal 'form_config_APAR') dari tabel `master_data`
    public function destroyMasterCategory($category_name)
    {
        $deletedCount = MasterData::where('category', $category_name)->delete();
        
        if ($deletedCount > 0) {
            return redirect()->back()->with('message', 'Kategori konfigurasi "' . ucfirst(str_replace('_', ' ', $category_name)) . '" dan ' . $deletedCount . ' field berhasil dihapus!');
        } else {
            return redirect()->back()->with('error', 'Kategori konfigurasi "' . ucfirst(str_replace('_', ' ', $category_name)) . '" tidak ditemukan atau tidak ada field yang dihapus.');
        }
    }


    // BARU: API untuk mendapatkan konfigurasi form input untuk tipe tertentu (untuk mobile)
    public function getFormConfigsForMobile($form_type)
    {
        try {
            // PENTING: HAPUS 'options_category' DARI SELECT STATEMENT INI
            $configs = MasterData::select(
                                    'value as field_name',
                                    'label_display',
                                    'input_type',
                                    'is_required',
                                    'field_order'
                                )
                                ->where('category', $form_type)
                                ->orderBy('field_order')
                                ->get();
            return response()->json($configs);

        } catch (Throwable $e) {
            \Log::error("Error fetching form configs for $form_type: " . $e->getMessage(), [
                'exception' => $e, 'request_url' => request()->fullUrl()
            ]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat mengambil konfigurasi form.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function pemeliharaan()
    {
        $pengajuanPending = PengajuanBarang::select('report_id', 'nama_laporan','status', \DB::raw('COUNT(*) as total_items'), \DB::raw('MIN(created_at) as created_at'))
                                          ->where('status', 'proses')
                                          ->groupBy('report_id', 'status', 'nama_laporan')
                                          ->orderBy('created_at', 'desc')
                                          ->get();

        $judul = 'Pemeliharaan';

        return view('supervisor.pemeliharaan.pemeliharaan', compact('pengajuanPending', 'judul'));
    }

    // Validasi Barang Masuk (untuk halaman ringkasan laporan)
    public function validasiBarangMasuk()
    {
        $pengajuanPending = PengajuanBarang::select('report_id', 'nama_laporan','status', \DB::raw('COUNT(*) as total_items'), \DB::raw('MIN(created_at) as created_at'))
                                          ->where('status', 'proses')
                                          ->groupBy('report_id', 'status', 'nama_laporan')
                                          ->orderBy('created_at', 'asc')
                                          ->get();

        $judul = 'Validasi Laporan Masuk';

        return view('supervisor.validasi_barang_masuk', compact('pengajuanPending', 'judul'));
    }

    public function validasiBarangKeluar()
    {
        $keluars = Transaksi::select(
                'report_id',
                'status',
                DB::raw('COUNT(*) as total_items'),
                DB::raw('MIN(created_at) as created_at')
            )
            ->where('status', 'keluar') // Status laporan yang baru dibuat dari mobile
            ->whereNotNull('report_id')
            ->groupBy('report_id', 'status')
            ->orderBy('created_at', 'desc')
            ->get();
    
        $judul = 'Validasi Laporan Keluar';
    
        return view('supervisor.validasi_barang_keluar', compact('keluars', 'judul'));
    }

    public function terimaKeluar($id)
    {
        $transaksi = Transaksi::findOrFail($id);

        $barang = Barang::where('id_barang', $transaksi->id_barang)->first();

        if (!$barang || $barang->jumlah_barang < $transaksi->jumlah_barang) {
            return back()->with('error', 'Stok tidak mencukupi.');
        }

        // Kurangi stok
        $barang->decrement('jumlah_barang', $transaksi->jumlah_barang);

        $transaksi->status = 'diterima';
        $transaksi->updated_at = now();
        $transaksi->save();

        return redirect()->route('supervisor.validasi.barang_keluar')
            ->with('message', 'Laporan diterima.');
    }

    public function tolakKeluar(Request $request, $id)
    {
        $transaksi = Transaksi::findOrFail($id);

        $transaksi->status = 'ditolak';
        $transaksi->catatan = $request->catatan_penolakan;
        $transaksi->created_at = now();
        $transaksi->updated_at = now();
        $transaksi->save();

        return redirect()->route('supervisor.validasi.barang_keluar')
            ->with('message', 'Laporan ditolak dengan catatan.');
    }

    public function showKeluarDetail($reportId) // Nama parameter diubah agar lebih jelas
    {
        // Ambil semua item transaksi yang memiliki report_id yang sama
        $keluar = DB::table('transaksis')
            ->join('barangs', 'transaksis.id_barang', '=', 'barangs.id_barang')
            ->select('transaksis.*', 'barangs.nama_barang')
            ->where('report_id', $reportId)
            ->orderBy('id_transaksi')
            ->get();

        // PERBAIKAN: Tambahkan pengecekan jika laporan tidak ditemukan
        if ($keluar->isEmpty()) {
            // Jika tidak ada data, tampilkan halaman error 404
            abort(404, 'Laporan barang keluar tidak ditemukan.');
        }

        $judul = 'Detail Laporan Barang Keluar';

        // Kirim data ke view dengan nama variabel yang lebih jelas
        return view('supervisor.validasi_barang_keluar_detail', compact('judul', 'keluar', 'reportId'));
    }
    
    // Metode untuk menampilkan detail laporan yang akan divalidasi
    public function lihatDetailLaporan($reportId)
    {
        $itemsInReport = PengajuanBarang::where('report_id', $reportId)->orderBy('id')->get();

        if ($itemsInReport->isEmpty()) {
            abort(404, 'Laporan tidak ditemukan.');
        }

        $judul = 'Detail Laporan Barang Masuk';

        // Ambil nama_laporan dari item pertama
        $nama_laporan = $itemsInReport->first()->nama_laporan ?? 'Laporan';

        return view('supervisor.validasi_laporan_detail', compact('itemsInReport', 'judul', 'reportId', 'nama_laporan'));
    }

    // Metode untuk memvalidasi (menerima atau menolak) pengajuan
    public function validasiPengajuan(Request $request)
    {
        $request->validate([
            'report_id' => 'required|string',
            'aksi' => 'required|in:terima,tolak',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $reportId = $request->input('report_id');
        $aksi = $request->input('aksi');

        DB::beginTransaction();
        try {
            if ($aksi == 'terima') {
                $items = PengajuanBarang::where('report_id', $reportId)->get();
                $barangModel = new Barang();
                $fillableBarangColumns = $barangModel->getFillable();

                $columnMapping = [
                    'kondisi' => 'kondisi_barang',
                    'berat_barang' => 'berat',
                    'merek_barang' => 'merek',
                    'media' => 'media',
                ];

                foreach ($items as $pengajuan) {
                    $barangData = [];

                    foreach ($fillableBarangColumns as $barangColumn) {
                        $sumberColumn = $columnMapping[$barangColumn] ?? $barangColumn;
                        if (isset($pengajuan->{$sumberColumn})) {
                            $barangData[$barangColumn] = $pengajuan->{$sumberColumn};
                        }
                    }

                    // Normalisasi teks (hindari duplikat karena beda kapitalisasi)
                    if (isset($barangData['nama_barang'])) {
                        $barangData['nama_barang'] = ucwords(strtolower($barangData['nama_barang']));
                    }
                    if (isset($barangData['merek_barang'])) {
                        $barangData['merek_barang'] = ucwords(strtolower($barangData['merek_barang']));
                    }
                    if (isset($barangData['tipe_barang'])) {
                        $barangData['tipe_barang'] = strtoupper($barangData['tipe_barang']);
                    }

                    // Cek apakah barang sudah ada dengan data yang sama (nama + merek + tipe + berat)
                    $barangSudahAda = Barang::where('nama_barang', $barangData['nama_barang'] ?? '')
                        ->where('merek_barang', $barangData['merek_barang'] ?? '')
                        ->where('tipe_barang', $barangData['tipe_barang'] ?? '')
                        ->where('berat_barang', $barangData['berat_barang'] ?? null)
                        ->exists();

                    if (!$barangSudahAda) {
                        // Generate ID unik
                        $prefix = strtoupper(substr(trim($pengajuan->tipe_barang_kategori), 0, 3));
                        $product = 'GEN';
                        if (!empty(trim($pengajuan->tipe_barang ?? ''))) {
                            $product = strtoupper(substr(trim($pengajuan->tipe_barang), 0, 3));
                        }
                        $monthYear = date('my');
                        $fullPrefix = "{$prefix}{$product}-{$monthYear}-";

                        $count = Barang::where('id_barang', 'LIKE', "{$fullPrefix}%")->count() + 1;
                        $increment = str_pad($count, 3, '0', STR_PAD_LEFT);
                        $barangData['id_barang'] = "{$fullPrefix}{$increment}";

                        // Simpan ke database
                        Barang::create($barangData);
                    }
                }

                PengajuanBarang::where('report_id', $reportId)->update(['status' => 'diterima']);

                DB::commit();
                return redirect()->route('supervisor.validasi.barang_masuk')
                    ->with('message', 'Laporan berhasil diterima dan barang telah ditambahkan ke stok!');
            }

            if ($aksi == 'tolak') {
                $catatan = $request->input('catatan_penolakan') ?: 'Laporan ditolak oleh Supervisor.';
                PengajuanBarang::where('report_id', $reportId)->update([
                    'status' => 'ditolak',
                    'catatan_penolakan' => $catatan,
                ]);

                DB::commit();
                return redirect()->route('supervisor.validasi.barang_masuk')
                    ->with('message', 'Laporan berhasil ditolak!');
            }

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Validasi Pengajuan Gagal: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return redirect()->back()->with('error', 'Terjadi kesalahan. Silakan cek log untuk detail.');
        }
    }

    public function validasiPengajuanKeluar(Request $request)
    {
        $request->validate([
            'report_id' => 'required|string',
            'aksi' => 'required|in:terima,tolak',
            'catatan_penolakan' => 'nullable|string'
        ]);

        $reportId = $request->input('report_id');
        $aksi = $request->input('aksi');

        DB::beginTransaction();
        try {
            if ($aksi === 'terima') {

                $items = PengajuanBarang::where('report_id', $reportId)->get();

                // Pastikan ada Transaksi parent
                $transaksi = Transaksi::firstOrCreate(
                    ['report_id' => $reportId],
                    ['status' => 'memproses']
                );

                foreach ($items as $item) {
                    $barang = Barang::where('nama_barang', $item->nama_barang)->first();
                    if ($barang) {
                        $barang->jumlah_barang += $item->jumlah_barang;
                        $barang->save();
                    } else {
                        $barang = Barang::create([
                            'nama_barang' => $item->nama_barang,
                            'jumlah_barang' => $item->jumlah_barang,
                            // Tambahkan kolom lain sesuai keperluan
                        ]);
                    }

                    // Hubungkan ke transaksi detail
                    $transaksi->barangKeluar()->create([
                        'barang_id' => $barang->id,
                        'jumlah_keluar' => $item->jumlah_barang,
                    ]);
                }

                PengajuanBarang::where('report_id', $reportId)
                    ->update(['status' => 'diterima']);

                $transaksi->update(['status' => 'diterima']);

                DB::commit();
                return redirect()->route('supervisor.validasi.barang_keluar')
                    ->with('message', 'Laporan berhasil diterima & barang keluar dicatat ke transaksi.');
            }

            if ($aksi === 'tolak') {
                $catatan = $request->input('catatan_penolakan') ?: 'Laporan ditolak.';
                Transaksi::where('report_id', $reportId)
                    ->update(['status' => 'ditolak', 'catatan_penolakan' => $catatan]);

                PengajuanBarang::where('report_id', $reportId)
                    ->update(['status' => 'ditolak']);

                DB::commit();
                return redirect()->route('supervisor.validasi.barang_keluar')
                    ->with('message', 'Laporan berhasil ditolak.');
            }

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Validasi Pengajuan Keluar Gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan.');
        }
    }

    public function logAktivitas(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->start_date, fn($q) =>
                $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) =>
                $q->whereDate('created_at', '<=', $request->end_date))
            ->latest()
            ->paginate(30);
        
        $judul = 'Log Aktivitas';

        return view('supervisor.log_aktivitas', compact('logs','judul'));
    }

    public function riwayat(Request $request)
    {
        $judul = 'Riwayat';

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $transaksis = Transaksi::where('status', '!=', 'proses')
            ->with('barang')
            ->get();

        $pengajuans = PengajuanBarang::where('status', '!=', 'proses')
            ->where('status','!=','proses')
            ->with('barang') // relasi yang benar
            ->get();

        if ($startDate) {
            $transaksis = $transaksis->where('created_at', '>=', $startDate);
            $pengajuans = $pengajuans->where('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $transaksis = $transaksis->where('created_at', '<=', $endDate);
            $pengajuans = $pengajuans->where('created_at', '<=', $endDate);
        }

        $riwayatGabung = $transaksis->merge($pengajuans)->sortByDesc('created_at');

        return view('supervisor.riwayat', compact('judul', 'riwayatGabung'));
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