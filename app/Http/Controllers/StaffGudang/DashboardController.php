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
    public function inputDataBarang()
    {
        $defaultBarangJadi = collect([
            ['value' => 'nama_barang', 'label_display' => 'Nama Barang', 'input_type' => 'text', 'is_required' => true],
            ['value' => 'tipe_barang', 'label_display' => 'Tipe Barang', 'input_type' => 'text', 'is_required' => true],
            ['value' => 'jumlah_barang', 'label_display' => 'Jumlah Barang', 'input_type' => 'number', 'is_required' => true],
            ['value' => 'jenis_barang', 'label_display' => 'Jenis Barang', 'input_type' => 'text', 'is_required' => false],
            ['value' => 'kondisi', 'label_display' => 'Kondisi', 'input_type' => 'text', 'is_required' => false],
            ['value' => 'media', 'label_display' => 'Media', 'input_type' => 'text', 'is_required' => false],
            ['value' => 'berat', 'label_display' => 'Berat', 'input_type' => 'number', 'is_required' => false],
            ['value' => 'satuan', 'label_display' => 'Satuan', 'input_type' => 'text', 'is_required' => false],
        ]);

        $defaultSparepart = collect([
            ['value' => 'nama_barang', 'label_display' => 'Nama Barang', 'input_type' => 'text', 'is_required' => true],
            ['value' => 'tipe_barang', 'label_display' => 'Tipe Barang', 'input_type' => 'text', 'is_required' => true],
            ['value' => 'jumlah_barang', 'label_display' => 'Jumlah Barang', 'input_type' => 'number', 'is_required' => true],
            ['value' => 'ukuran_barang', 'label_display' => 'Ukuran Barang', 'input_type' => 'text', 'is_required' => false],
            ['value' => 'satuan', 'label_display' => 'Satuan', 'input_type' => 'text', 'is_required' => false],
            ['value' => 'kondisi', 'label_display' => 'Kondisi', 'input_type' => 'text', 'is_required' => false],
        ]);

        $masterFields = MasterData::query()
            ->whereIn('category', ['APAR', 'HYDRANT', 'Sparepart'])
            ->where('is_active', true)
            ->orderBy('field_order')
            ->get()
            ->groupBy('category');

        $formConfigs = [
            'APAR' => $masterFields->get('APAR', $defaultBarangJadi)->values(),
            'HYDRANT' => $masterFields->get('HYDRANT', $masterFields->get('APAR', $defaultBarangJadi))->values(),
            'Sparepart' => $masterFields->get('Sparepart', $defaultSparepart)->values(),
        ];

        return view('staff_gudang.input_data_barang', [
            'judul' => 'Input Data Barang',
            'formConfigs' => $formConfigs,
        ]);
    }

    public function storeInputDataBarang(Request $request)
    {
        $validatedType = $request->validate([
            'tipe_barang' => 'required|in:Barang Jadi,Sparepart',
        ]);

        if ($validatedType['tipe_barang'] === 'Sparepart') {
            $request->validate(['tipe_barang_kategori' => 'prohibited']);
            $category = null;
            $formCategory = 'Sparepart';
        } else {
            $category = $request->validate([
                'tipe_barang_kategori' => 'required|in:APAR,HYDRANT',
            ])['tipe_barang_kategori'];
            $formCategory = $category;
        }

        $formFields = MasterData::query()
            ->whereRaw('UPPER(category) = ?', [strtoupper($formCategory)])
            ->where('is_active', true)
            ->orderBy('field_order')
            ->get();

        if ($formFields->isEmpty() && $formCategory === 'HYDRANT') {
            $formFields = MasterData::query()
                ->whereRaw('UPPER(category) = ?', ['APAR'])
                ->where('is_active', true)
                ->orderBy('field_order')
                ->get();
        }

        $rules = [
            'nama_barang' => 'required|string|max:255',
            'jumlah_barang' => 'required|integer|min:1',
            'jenis_barang' => 'nullable|string|max:255',
            'kondisi' => 'nullable|string|max:255',
            'media' => 'nullable|string|max:255',
            'berat' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'ukuran_barang' => 'nullable|string|max:255',
            'panjang' => 'nullable|numeric|min:0',
            'lebar' => 'nullable|numeric|min:0',
            'tinggi' => 'nullable|numeric|min:0',
            'tanggal_kadaluarsa' => 'nullable|date',
            'merek' => 'nullable|string|max:255',
        ];

        if ($category !== null) {
            $rules['tipe_barang_kategori'] = 'required|in:APAR,HYDRANT';
        }

        foreach ($formFields as $field) {
            $fieldName = $field->value === 'kondisi_barang' ? 'kondisi' : $field->value;
            if (!array_key_exists($fieldName, $rules) || !$field->is_required) {
                continue;
            }

            $rules[$fieldName] = preg_replace('/^nullable/', 'required', $rules[$fieldName]);
        }

        $validated = $request->validate($rules);
        $fieldMapping = [
            'kondisi' => 'kondisi_barang',
            'berat' => 'berat',
        ];
        $pengajuanData = [
            'report_id' => PengajuanBarang::generateUniqueReportId(),
            'nama_laporan' => 'Laporan Barang Masuk',
            'tipe_barang' => $validatedType['tipe_barang'],
            'tipe_barang_kategori' => $category,
            'status' => 'proses',
            'created_by_id' => auth()->id(),
            'created_by_role' => auth()->user()->role,
        ];

        foreach ($fieldMapping + array_fill_keys(array_keys($rules), null) as $inputName => $column) {
            if (!array_key_exists($inputName, $validated)) {
                continue;
            }

            $pengajuanData[$column ?? $inputName] = $validated[$inputName];
        }

        PengajuanBarang::create($pengajuanData);

        return redirect()->route('staff_gudang.input_data')
            ->with('message', 'Data barang berhasil dikirim untuk validasi.');
    }

    public function formBarangKeluar()
    {
        $barang = Barang::query()
            ->where('jumlah_barang', '>', 0)
            ->orderBy('nama_barang')
            ->get(['id_barang', 'nama_barang', 'jumlah_barang', 'satuan']);

        return view('staff_gudang.catat_barang_keluar', [
            'judul' => 'Catat Barang Keluar',
            'barang' => $barang,
        ]);
    }

    public function storeBarangKeluar(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_barang' => 'required|string|distinct|exists:barangs,id_barang',
            'items.*.jumlah_barang' => 'required|integer|min:1',
            'tujuan' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:2000',
        ]);

        $barangById = Barang::query()
            ->whereIn('id_barang', collect($validated['items'])->pluck('id_barang'))
            ->get()
            ->keyBy('id_barang');

        foreach ($validated['items'] as $index => $item) {
            $barang = $barangById->get($item['id_barang']);
            if ($item['jumlah_barang'] > $barang->jumlah_barang) {
                return back()
                    ->withErrors([
                        "items.{$index}.jumlah_barang" => "Jumlah {$barang->nama_barang} melebihi stok tersedia ({$barang->jumlah_barang}).",
                    ])
                    ->withInput();
            }
        }

        $reportId = PengajuanBarang::generateUniqueReportId('PBK-' . now()->format('ymd') . '-', 4);

        DB::transaction(function () use ($validated, $barangById, $reportId) {
            foreach ($validated['items'] as $item) {
                $barang = $barangById->get($item['id_barang']);

                PengajuanBarang::create([
                    'report_id' => $reportId,
                    'id_barang' => $barang->id_barang,
                    'nama_laporan' => 'Laporan Barang Keluar',
                    'nama_barang' => $barang->nama_barang,
                    'tipe_barang' => $barang->tipe_barang,
                    'tipe_barang_kategori' => $barang->tipe_barang_kategori,
                    'jumlah_barang' => $item['jumlah_barang'],
                    'status' => 'proses',
                    'catatan_penolakan' => '-',
                    'created_by_id' => auth()->id(),
                    'created_by_role' => auth()->user()->role,
                ]);

                Transaksi::create([
                    'report_id' => $reportId,
                    'id_barang' => $barang->id_barang,
                    'jumlah_barang' => $item['jumlah_barang'],
                    'tujuan' => $validated['tujuan'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'status' => 'keluar',
                    'catatan_penolakan' => '-',
                ]);
            }
        });

        return redirect()->route('staff_gudang.barang_keluar.create')
            ->with('message', count($validated['items']) . ' jenis barang berhasil diajukan dan menunggu validasi. ID laporan: ' . $reportId);
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

        $barangDiterima = Barang::with('qrCodes')
            ->orderBy('created_at', 'desc')
            ->get();
        $judul = 'Data Barang';

        return view('staff_gudang.data_barang', compact('barangDiterima', 'judul'));
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

    public function previewQr($id, Request $request)
    {
        $barang = Barang::where('id_barang', $id)->firstOrFail();
        $mode = $request->input('mode', 'create');

        if ($mode === 'regenerate') {
            $qrRecord = $request->filled('qr_id')
                ? QrCodeModel::find($request->qr_id)
                : QrCodeModel::where('id_barang', $id)->latest('tanggal_pembuatan')->first();

            if (!$qrRecord) {
                return response()->json([
                    'status' => 'empty',
                    'message' => 'QR yang akan di-generate ulang tidak ditemukan.'
                ]);
            }

            $nomorIdentifikasiBaru = $qrRecord->nomor_identifikasi;
        } else {
            $jumlahSudahAda = QrCodeModel::where('id_barang', $id)->count();
            if ($jumlahSudahAda >= $barang->jumlah_barang) {
                return response()->json([
                    'status' => 'full',
                    'message' => 'Semua QR untuk barang ini sudah dibuat.'
                ]);
            }

            $prefix = $barang->id_barang;

            $lastQr = QrCodeModel::where('nomor_identifikasi', 'like', $prefix . '-%')
                ->orderByRaw("
                    CAST(SUBSTRING_INDEX(nomor_identifikasi, '-', -1) AS UNSIGNED) DESC
                ")
                ->first();

            $lastNumber = $lastQr
                ? (int) substr($lastQr->nomor_identifikasi, -3)
                : 0;

            $nextNumber = str_pad(++$lastNumber, 3, '0', STR_PAD_LEFT);
            $nomorIdentifikasiBaru = $prefix . '-' . $nextNumber;
        }

        $qrContent = implode("\n", [
            'ID Barang: ' . $barang->id_barang,
            'Nama Barang: ' . $barang->nama_barang,
            'Nomor Identifikasi: ' . $nomorIdentifikasiBaru,
            'Tanggal Pembuatan: ' . now()->format('d-m-Y H:i:s'),
        ]);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->size(300)
            ->margin(10)
            ->build();

        $base64 = base64_encode($result->getString());

        return response()->json([
            'status' => 'preview',
            'mode' => $mode,
            'qr_id' => $request->input('qr_id') ?? ($mode === 'regenerate' ? ($qrRecord->id_qr_code ?? null) : null),
            'qr_image' => 'data:image/png;base64,' . $base64,
            'nomor_identifikasi' => $nomorIdentifikasiBaru
        ]);
    }

    public function storeQr(Request $request)
    {
        $barang = Barang::where('id_barang', $request->id)->firstOrFail();
        $mode = $request->input('mode', 'create');

        if ($mode === 'regenerate') {
            $qr = $request->filled('qr_id')
                ? QrCodeModel::find($request->qr_id)
                : QrCodeModel::where('id_barang', $request->id)->latest('tanggal_pembuatan')->first();

            if (!$qr) {
                return response()->json(['status' => 'error', 'message' => 'QR lama tidak ditemukan.']);
            }

            $timestamp = now()->format('YmdHis');
            $fileName = $timestamp . '_' . $request->nomor_identifikasi . '.png';
            $path = 'qrcodes/' . $fileName;

            Storage::disk('public')->delete($qr->qr_code_path);
            Storage::disk('public')->put($path, base64_decode($request->image));

            $qr->update([
                'nomor_identifikasi' => $request->nomor_identifikasi,
                'qr_code_path' => $path,
                'tanggal_pembuatan' => now(),
                'status_qr' => 'sudah_generate'
            ]);

            return response()->json([
                'status' => 'success',
                'url' => Storage::disk('public')->url($path),
                'fileName' => $fileName,
                'mode' => 'regenerate'
            ]);
        }

        $jumlahSudahAda = QrCodeModel::where('id_barang', $request->id)->count();
        if ($jumlahSudahAda >= $barang->jumlah_barang) {
            return response()->json(['status' => 'full']);
        }

        $timestamp = now()->format('YmdHis');
        $fileName = $timestamp . '_' . $request->nomor_identifikasi . '.png';
        $path = 'qrcodes/' . $fileName;

        Storage::disk('public')->put($path, base64_decode($request->image));

        QrCodeModel::create([
            'id_barang' => $barang->id_barang,
            'nomor_identifikasi' => $request->nomor_identifikasi,
            'qr_code_path' => $path,
            'tanggal_pembuatan' => now(),
            'status_qr' => 'sudah_generate'
        ]);

        return response()->json([
            'status' => 'success',
            'url' => Storage::disk('public')->url($path),
            'fileName' => $fileName,
            'mode' => 'create'
        ]);
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

        
        $riwayatGabung = $transaksis->concat($pengajuans)->sortByDesc('created_at')->values();
        $judul = 'Riwayat';

        
        return view('staff_gudang.riwayat', compact('logs', 'riwayatGabung', 'judul'));
    }

    public function pengajuanBarangs(Request $request)
    {
        
        $category = $request->input('tipe_barang_kategori');
        if (!$category) {
            return response()->json(['message' => 'Tipe barang kategori wajib diisi.'], 422);
        }

        
        $formConfigs = FormConfig::where('category', $category)->get();
        if ($formConfigs->isEmpty()) {
            return response()->json(['message' => "Konfigurasi form untuk kategori '{$category}' tidak ditemukan."], 404);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }
                
        $created_by_role = $user->role;
        $created_by_id = $user->id;

        
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

        
        $namaBarang = $validatedData['nama_barang'] ?? null;
        if (!$namaBarang) {
            return response()->json(['message' => 'Nama barang wajib diisi.'], 422);
        }

        $tipeBarang = $validatedData['tipe_barang_kategori'] ?? null;
        $barang = Barang::where('nama_barang', $namaBarang)
                        ->where('tipe_barang_kategori', $tipeBarang)->first();

        if ($barang) {
            
            $idBarang = $barang->id_barang;
        } else {
            
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

        
        $dataToSave['nama_laporan'] = 'Laporan Barang Masuk';
        $dataToSave['status'] = '-';
        $dataToSave['catatan_penolakan'] = '-';

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
            $reportId = PengajuanBarang::generateUniqueReportId('PBK-' . date('ymd') . '-', 4);

            foreach ($items as $item) {
                PengajuanBarang::create([
                    'report_id' => $reportId,
                    'id_barang' => $item['id_barang'],
                    'jumlah_barang' => $item['jumlah_barang'],
                    'tujuan' => $commonData['tujuan'] ?? null,
                    'keterangan' => $commonData['keterangan'] ?? null,
                    'status' => 'proses', 
                    'catatan_penolakan' => '-',
                ]);
            }

            
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