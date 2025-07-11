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
                \DB::raw('SUM(jumlah_barang) as total_stok'), // Menjumlahkan stok
                \DB::raw('GROUP_CONCAT(DISTINCT tipe_barang) as jenis_barang'), // Mengumpulkan semua tipe barang unik
                \DB::raw('GROUP_CONCAT(DISTINCT satuan) as satuan'), // Mengumpulkan semua berat unik
                \DB::raw('ANY_VALUE(harga_beli) as harga_beli'), // Mengambil salah satu harga beli (asumsi harga sama untuk nama barang yang sama)
                \DB::raw('ANY_VALUE(harga_jual) as harga_jual'), // Mengambil salah satu harga jual
                \DB::raw('ANY_VALUE(id_barang) as id_representatif') // Ambil satu ID sebagai representasi untuk modal
            )
            ->groupBy('nama_barang', 'tipe_barang', 'berat_barang')
            ->orderBy('nama_barang')
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
            'page_accessed' => 'Data Barang Diterima',
            'feature_used' => 'Halaman daftar barang',
            'action' => 'View',
            'description' => 'Staff melihat daftar barang masuk yang diterima.'
        ]);

        $barangDiterima = Barang::orderBy('created_at', 'asc')->get();
        $judul = 'Data Barang Diterima';

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

        $pengajuanPending = PengajuanBarang::where('status', 'pending')->orderBy('created_at', 'desc')->get();
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

    public function generateQrCode($id)
    {
        $fileName = 'qr_' . $id . '_' . time() . '.png';
        $path = 'qrcodes/' . $fileName;

        $qrContentArray = [
            'Data Barang ID: ' . $id,
            'Nomor Identifikasi: ' . 'BARANG-' . $id,
            'QrCode path: ' . $path,
            'Tanggal Pembuatan: ' . now(),
        ];

        Storage::disk('public')->makeDirectory('qrcodes');
        $qrContent = implode("\n", $qrContentArray);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->size(300)
            ->build();

        Storage::disk('public')->put($path, $result->getString());

        QrCodeModel::updateOrCreate(
            ['id_barang' => $id],
            [
                'nomor_identifikasi' => 'BARANG-' . $id,
                'qr_code_path' => $path,
                'tanggal_pembuatan' => now()
            ]
        );

        $url = asset('storage/' . $path);

        return response()->json(['url' => $url], 200);
    }

    public function formPengajuan()
    {
        // Ambil barang-barang yang kondisinya selain 'Bagus'
        $barangTidakBagus = Barang::where('kondisi', '!=', 'Bagus')
                                  ->where('status', 'pengajuan')
                                  ->orderBy('kondisi', 'asc') // Urutkan berdasarkan kondisi
                                  ->orderBy('nama_barang', 'asc')
                                  ->get();
        $judul = 'Form Pengajuan';

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

            if ($jenisLaporan === 'Laporan Kondisi Barang') {
                $barangRusak = Barang::where('kondisi', '!=', 'Bagus')
                          ->where(function($q) {
                              $q->whereNull('status')->orWhere('status', '');
                          })
                          ->get();

                $tempPengajuanItems = collect();

                foreach ($barangRusak as $barangItem) {
                    $tempPengajuanItems->push(PengajuanBarang::create([
                        'nama_barang' => $barangItem->nama_barang ?? 'N/A',
                        'tipe_barang_kategori' => $barangItem->tipe_barang_kategori ?? 'N/A',
                        'tipe_barang' => $barangItem->tipe_barang ?? 'N/A',
                        'jumlah_barang' => $barangItem->jumlah_barang ?? 1,
                        'satuan' => $barangItem->satuan ?? 'N/A',
                        'kondisi_barang' => $barangItem->kondisi ?? 'Rusak',
                        'berat' => $barangItem->berat_barang ?? null,
                        'tanggal_kadaluarsa' => $barangItem->tanggal_kadaluarsa ?? null,
                        'ukuran_barang' => $barangItem->ukuran_barang ?? null,
                        'panjang' => $barangItem->panjang ?? null,
                        'lebar' => $barangItem->lebar ?? null,
                        'tinggi' => $barangItem->tinggi ?? null,
                        'merek' => $barangItem->merek ?? null,
                        'status' => 'pending',
                    ]));
                    $barangItem->status = 'proses_pengajuan';
                    $barangItem->save();
                }
                $itemsToProcess = $tempPengajuanItems;
            }

            if ($itemsToProcess->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Tidak ada laporan pending untuk dikirim.');
            }

            $reportId = Str::uuid()->toString();
            $now = now();

            foreach ($itemsToProcess as $pengajuan) {
                $pengajuan->status = 'proses';
                $pengajuan->report_id = $reportId;
                $pengajuan->save();
            }

            DB::commit();
            return redirect()->back()->with('message', count($itemsToProcess) . ' item dari "' . $jenisLaporan . '" berhasil dikirim untuk validasi Supervisor!');
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error saat mengirim laporan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function riwayat()
    {
        $logs = ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(10);
        $laporan = PengajuanBarang::whereIn('status', ['diterima', 'ditolak'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
        $judul = 'Riwayat';

        return view('staff_gudang.riwayat', compact('logs', 'laporan', 'judul'));
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