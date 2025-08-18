<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\Keluar;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ActivityLog;
use Carbon\Carbon;     
use App\Exports\ExcelExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\PengajuanBarang;
use App\Models\Transaksi;


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
        $barangKeluarCount = Transaksi::where('status', 'diterima') // hanya yang sudah divalidasi supervisor
            ->whereBetween('created_at', [$awalBulan, $akhirBulan])
            ->sum('jumlah_barang');

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
                                        ->take(5) // Ambil 5 data teratas
                                        ->get();

        $dataStokBarang = Barang::select(
            'nama_barang',
            \DB::raw('SUM(jumlah_barang) as stok')
        )
        ->groupBy('nama_barang')
        ->get();

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
                                          ->take(5);              
        
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


        $data = array(
            'judul' => 'Dashboard',
            'barang' => Barang::all(),
            'barangMasukBulanIni' => $barangMasukCount,
            'barangKeluarBulanIni' => $barangKeluarCount,
            'chart' => $chartData,
            'pengajuan' => $pengajuan,
            'riwayatLogin' => $riwayatLoginData,
            'laporanGabungan' => $laporanGabungan,
            'lowStockItems' => $lowStockItems,
        );

        $totalKeseluruhanBarang = Barang::sum('jumlah_barang');
        
        return view('supervisor.index', $data, compact('rs', 'fp', 'dataStokBarang', 'totalKeseluruhanBarang'));
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
        
        $lowStockItems = $barangAggregated->filter(function ($item) {
            // Ambil semua item yang total_stok nya kurang dari 10
            return $item->total_stok < 10;
        });

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
            'lowStockItems' => $lowStockItems,
        ]);
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

        $timestamp = now()->format('YmdHis');
        $filename = 'data_barang_' . $timestamp . '.xlsx';

        return Excel::download(new ExcelExport($formattedData), $filename);
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
        
        $timestamp = now()->format('YmdHis');
        $filename = 'monitoring_stok_barang_' . $timestamp . '.pdf';

        return $pdf->download($filename);
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

        return view('supervisor.riwayat', compact('judul', 'riwayatGabung'));
    }
}