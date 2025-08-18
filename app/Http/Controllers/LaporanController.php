<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Throwable;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Barang;
use Illuminate\Support\Facades\DB;
use App\Models\PengajuanBarang;
use App\Models\Transaksi;
use App\Models\ActivityLog;
use App\Models\LaporanApk;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LaporanController extends Controller
{
    // Validasi Barang Masuk (untuk halaman ringkasan laporan)
    public function validasiBarangMasuk()
    {
        $pengajuanPending = PengajuanBarang::select('report_id', 'nama_laporan','status', \DB::raw('COUNT(*) as total_items'), \DB::raw('MIN(created_at) as created_at'))
                                          ->where('status', 'proses')
                                          ->groupBy('report_id', 'status', 'nama_laporan')
                                          ->orderBy('created_at', 'desc')
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

                    $barangData['lokasi'] = '-';

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
                    if (isset($barangData['tipe_barang'])) {
                        $barangData['tipe_barang'] = strtoupper($barangData['tipe_barang']);
                    }

                    $barangData['merek_barang'] = $barangData['nama_barang'];

                    // Tambahkan created_by_id dan created_by_role dari pengajuan
                    $barangData['created_by_id']   = $pengajuan->created_by_id ?? auth()->id();
                    $barangData['created_by_role'] = $pengajuan->created_by_role ?? (auth()->user()->role ?? '-');

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
            $pengajuans = PengajuanBarang::where('report_id', $reportId)->get();

            if ($aksi === 'terima') {
                foreach ($pengajuans as $item) {
                    $barang = Barang::where('id_barang', $item->id_barang)->first();

                    if (!$barang || $barang->jumlah_barang < $item->jumlah_barang) {
                        DB::rollBack();
                        return redirect()->back()->with('error', 'Stok barang ' . ($barang->nama_barang ?? '-') . ' tidak mencukupi.');
                    }

                    // Kurangi stok barang
                    $barang->jumlah_barang -= $item->jumlah_barang;
                    $barang->save();
                }

                // Update status
                PengajuanBarang::where('report_id', $reportId)->update(['status' => 'diterima']);
                Transaksi::where('report_id', $reportId)->update(['status' => 'diterima']);

                DB::commit();
                return redirect()->route('supervisor.validasi.barang_keluar')
                    ->with('message', 'Pengajuan berhasil diterima & stok barang keluar tercatat.');
            }

            if ($aksi === 'tolak') {
                $catatan = $request->input('catatan_penolakan') ?: 'Pengajuan ditolak.';

                PengajuanBarang::where('report_id', $reportId)->update(['status' => 'ditolak']);
                Transaksi::where('report_id', $reportId)->update([
                    'status' => 'ditolak',
                    'catatan_penolakan' => $catatan,
                ]);

                DB::commit();
                return redirect()->route('supervisor.validasi.barang_keluar')
                    ->with('message', 'Pengajuan berhasil ditolak.');
            }

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Validasi Pengajuan Keluar Gagal: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan.');
        }
    }

    public function kirimLaporan(Request $request)
    {
        ActivityLog::create([
            'user_id'       => auth()->id(),
            'page_accessed' => 'Halaman Laporan',
            'feature_used'  => 'Kirim Laporan',
            'action'        => 'Update',
            'description'   => 'Staff mengirim laporan ke supervisor.'
        ]);

        DB::beginTransaction();
        try {
            // Ambil semua pengajuan pending
            $pengajuanPending = PengajuanBarang::where('status', '-')->get();

            if ($pengajuanPending->isEmpty()) {
                return redirect()->back()->with('error', 'Tidak ada laporan yang bisa dikirim.');
            }

            // Buat 1 report_id untuk semua data
            $reportId = 'RPT-' . strtoupper(Str::random(8));

            // Update semua data
            foreach ($pengajuanPending as $pengajuan) {
                $pengajuan->update([
                    'report_id' => $reportId,
                    'status'    => 'Proses'
                ]);
            }

            DB::commit();
            return redirect()->back()->with('message', $pengajuanPending->count() . ' laporan berhasil dikirim dengan ID ' . $reportId);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error saat mengirim laporan: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return redirect()->back()->with('error', 'Gagal mengirim laporan. Silakan hubungi administrator.');
        }
    }

    // Modifikasi metode kirimLaporan untuk bisa mengirim berbagai jenis laporan
    public function kirimLaporanPengajuan(Request $request)
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
            $jenisLaporan = $request->input('jenis_laporan', 'Laporan Kondisi Barang');
            $itemsToProcess = collect();

            if ($jenisLaporan === 'Laporan Kondisi Barang') {
                // Ambil barang selain "Bagus" atau yang sudah lebih dari 1 bulan
                $barangRusak = Barang::join('qr_codes', 'barangs.id_barang', '=', 'qr_codes.id_barang')
                    ->where(function ($query) {
                        $query->where('barangs.kondisi', '!=', 'Bagus')
                            ->orWhere('barangs.created_at', '<', Carbon::now()->subMonth());
                    })
                    ->select('barangs.*', 'qr_codes.nomor_identifikasi as id_qr')
                    ->get();

                foreach ($barangRusak as $barangItem) {
                    $laporan = LaporanApk::create([
                        'id_qr'          => $barangItem->id_qr ?? null,
                        'id_barang'      => $barangItem->id_barang ?? null,
                        'id_user'        => auth()->id(),
                        'username'       => auth()->user()->username ?? 'N/A',
                        'nama_barang'    => $barangItem->nama_barang ?? 'N/A',
                        'tipe_barang'    => $barangItem->tipe_barang ?? 'N/A',
                        'tanggal_inspeksi'=> Carbon::now()->toDateString(),
                        'lokasi_alat'    => $barangItem->lokasi_alat ?? '-',
                        'foto'           => $barangItem->foto ?? null,
                        'kondisi_fisik'  => $barangItem->kondisi ?? 'Rusak',
                        'selang'         => $barangItem->selang ?? '-',
                        'pressure_gauge' => $barangItem->pressure_gauge ?? '-',
                        'safety_pin'     => $barangItem->safety_pin ?? '-',
                        'tindakan'       => 'Perlu pemeliharaan',
                        'status'         => 'Pending',
                    ]);
                    $itemsToProcess->push($laporan);
                }
            }

            if ($itemsToProcess->isEmpty()) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Tidak ada item yang perlu dilaporkan.');
            }

            DB::commit();
            return redirect()->back()->with('message', count($itemsToProcess) . ' item berhasil dikirim ke laporan_apk!');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error saat mengirim laporan: ' . $e->getMessage() . ' di baris ' . $e->getLine());
            return redirect()->back()->with('error', 'Gagal mengirim laporan. Silakan hubungi administrator.');
        }
    }

    public function formPengajuan()
    {
        // Ambil ID barang yang sudah ada di laporan_apk
        $barangSudahDilaporkan = LaporanApk::pluck('id_barang')->toArray();

        // Barang selain "Bagus" dan punya QR, yang belum pernah dilaporkan
        $barangKondisiBuruk = Barang::join('qr_codes', 'barangs.id_barang', '=', 'qr_codes.id_barang')
            ->where('barangs.kondisi', '!=', 'Bagus')
            ->whereNotIn('barangs.id_barang', $barangSudahDilaporkan)
            ->select('barangs.*', 'qr_codes.nomor_identifikasi as id_qr')
            ->get();

        // Barang "Bagus" tapi lebih dari 1 bulan dan punya QR, yang belum pernah dilaporkan
        $barangBagusLama = Barang::join('qr_codes', 'barangs.id_barang', '=', 'qr_codes.id_barang')
            ->where('barangs.kondisi', 'Bagus')
            ->where('barangs.created_at', '<', Carbon::now()->subMonth())
            ->whereNotIn('barangs.id_barang', $barangSudahDilaporkan)
            ->select('barangs.*', 'qr_codes.nomor_identifikasi as id_qr')
            ->get();

        $barangTidakBagus = $barangKondisiBuruk
            ->merge($barangBagusLama)
            ->unique('id_barang')
            ->sortByDesc('kondisi')
            ->sortByDesc('nama_barang');

        $judul = 'Halaman Pengajuan Pemeliharaan';

        return view('staff_gudang.pengajuan', compact('barangTidakBagus', 'judul'));
    }

    public function riwayatMasukDetail($reportId){
        try {
            // Cari semua item yang memiliki report_id yang sama
            $items = PengajuanBarang::where('report_id', $reportId)
                ->select('nama_barang', 'jumlah_barang', 'tipe_barang_kategori', 'catatan_penolakan') // Ambil hanya kolom yang dibutuhkan
                ->get();

            if ($items->isEmpty()) {
                // Jika tidak ada data, kirim respons 'not found'
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Jika berhasil, kirim data sebagai JSON
            return response()->json(['success' => true, 'data' => $items]);

        } catch (\Exception $e) {
            // Jika terjadi error server, kirim respons error
            // Sebaiknya log error ini untuk debugging
            // Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server'], 500);
        }
    }

    public function riwayatKeluarDetail($reportId) // Nama parameter diubah agar lebih jelas
    {
        try {
            // Cari semua item yang memiliki id_transaksi yang sama
            $items = Transaksi::where('id_transaksi', $reportId)
                ->select('nama_barang', 'jumlah_barang', 'tujuan', 'catatan_penolakan') // Pastikan nama kolom ini ada di tabel Transaksi
                ->get();
            
            if ($items->isEmpty()) {
                // Jika tidak ada data, kirim respons 'not found'
                return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
            }

            // Jika berhasil, kirim data sebagai JSON
            return response()->json(['success' => true, 'data' => $items]);

        } catch (\Exception $e) {
            // Jika terjadi error server, kirim respons error
            // Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan pada server'], 500);
        }
    }
}
