<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterData;
use Illuminate\Support\Facades\Log;
use Throwable;
use Illuminate\Support\Facades\Validator;

class MasterDataController extends Controller{

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
        // Validasi menggunakan Validator
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:50',
            'value' => 'required|string|max:255',
            'label_display' => 'required|string|max:255',
            'input_type' => 'required|string|in:text,number,dropdown,date',
            'field_order' => 'required|integer',
            'is_required' => 'nullable|boolean', // Gunakan nullable|boolean untuk checkbox
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422); // 422 Unprocessable Entity
        }

        // Cek duplikasi data
        $existing = MasterData::where('category', $request->category)
                                ->where('value', $request->value)
                                ->first();
        if ($existing) {
            return response()->json(['error' => 'Field "' . $request->value . '" sudah ada untuk kategori "' . $request->category . '".'], 409); // 409 Conflict
        }

        try {
            MasterData::create([
                'category' => $request->category,
                'value' => $request->value,
                'label_display' => $request->label_display,
                'input_type' => 'text',
                'field_order' => $request->field_order,
                'is_required' => $request->has('is_required'), // has() mengembalikan true/false
                'is_active' => true,
            ]);
            return response()->json(['message' => 'Konfigurasi field berhasil ditambahkan!'], 201); // 201 Created

        } catch (Throwable $e) {
            Log::error('Error storing MasterData: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Terjadi kesalahan server saat menambahkan data.'], 500);
        }
    }

    // Metode untuk update data master (Update) - Akan mengupdate konfigurasi field
    public function updateMasterData(Request $request, MasterData $masterData)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string|max:255|unique:master_data,value,' . $masterData->id . ',id,category,' . $masterData->category,
            'label_display' => 'required|string|max:255',
            'input_type' => 'required|string|in:text,number,dropdown,date',
            'field_order' => 'required|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        try {
            $masterData->value = $request->input('value');
            $masterData->label_display = $request->input('label_display');
            $masterData->input_type = $request->input('input_type');
            $masterData->field_order = $request->input('field_order');
            $masterData->is_required = $request->has('is_required'); // is_required from checkbox
            $masterData->is_active = $request->has('is_active'); // is_active from checkbox
            $masterData->save();

            return response()->json(['message' => 'Konfigurasi field berhasil diperbarui!'], 200);

        } catch (Throwable $e) {
            Log::error('Error updating MasterData: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Terjadi kesalahan server saat memperbarui data.'], 500);
        }
    }

    // Metode untuk menghapus nilai spesifik dari master_data (tetap sama)
    public function destroyMasterData(MasterData $masterData)
    {
        try {
            $masterData->delete();
            // Kembalikan JSON untuk sukses penghapusan
            return response()->json(['message' => 'Konfigurasi field berhasil dihapus!'], 200);

        } catch (Throwable $e) {
            Log::error('Error deleting MasterData item: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Terjadi kesalahan server saat menghapus data.'], 500);
        }
    }

    // Metode untuk menghapus SELURUH KATEGORI (misal 'form_config_APAR') dari tabel `master_data`
    public function destroyMasterCategory($category_name)
    {
        try {
            $deletedCount = MasterData::where('category', $category_name)->delete();
            
            if ($deletedCount > 0) {
                return response()->json(['message' => 'Kategori konfigurasi "' . ucfirst(str_replace('_', ' ', $category_name)) . '" dan ' . $deletedCount . ' field berhasil dihapus!'], 200);
            } else {
                return response()->json(['message' => 'Kategori konfigurasi "' . ucfirst(str_replace('_', ' ', $category_name)) . '" tidak ditemukan atau tidak ada field yang dihapus.'], 404);
            }
        } catch (Throwable $e) {
            Log::error('Error deleting MasterData category: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['error' => 'Terjadi kesalahan server saat menghapus kategori.'], 500);
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
}
