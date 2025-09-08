<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController as WebLoginController; // Alias untuk kejelasan
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Supervisor\Pemeliharaan;
use App\Http\Controllers\Supervisor\MasterDataController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\StaffGudang\DashboardController as StaffGudangDashboardController;
use App\Http\Controllers\LaporanController;

// Route untuk login WEB
Route::get('/login', [WebLoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [WebLoginController::class, 'login'])->middleware('guest'); // Akan ditangani oleh WebLoginController
Route::post('/logout', [WebLoginController::class, 'logout'])->name('logout')->middleware('auth');

// Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring-stok', [SupervisorDashboardController::class, 'tampil'])->name('monitoring');
    Route::post('/monitoring-stok/update-harga', [SupervisorDashboardController::class, 'updateHarga'])->name('updateHarga');
    Route::get('/exportcsv', [SupervisorDashboardController::class, 'exportExcel'])->name('exportExcel');
    Route::get('/export-pdf', [SupervisorDashboardController::class, 'exportPdf'])->name('exportPdf');

    //Master Data
    Route::get('/master-data', [MasterDataController::class, 'manageMasterData'])->name('master.data');
    Route::get('/master-data/{form_config_category}', [MasterDataController::class, 'manageSpecificMaster'])->name('master.data.specific');
    Route::post('/master-data-store', [MasterDataController::class, 'storeMasterData'])->name('master.data.store');
    Route::put('/master-data/{masterData}', [MasterDataController::class, 'updateMasterData'])->name('master.data.update');
    Route::delete('/master-data/{masterData}', [MasterDataController::class, 'destroyMasterData'])->name('master.data.destroy');
    Route::delete('/master-data/category/{category_name}', [MasterDataController::class, 'destroyMasterCategory'])->name('master.data.destroy_category');
    Route::get('/api/master-data/{category}', [MasterDataController::class, 'getMasterDataByCategory']);
    Route::delete('/master-data/category/{category_name}', [MasterDataController::class, 'destroyMasterCategory'])->name('master.data.destroy_category');
    //Master Data

    Route::get('/validasi-barang-masuk', [LaporanController::class, 'validasiBarangMasuk'])->name('validasi.barang_masuk'); // Halaman Supervisor
    Route::get('/validasi-laporan/{reportId}', [LaporanController::class, 'lihatDetailLaporan'])->name('validasi.laporan_detail');
    Route::post('/validasi-pengajuan', [LaporanController::class, 'validasiPengajuan'])->name('validasi.pengajuan');

    Route::get('/validasi/barang-keluar', [LaporanController::class, 'validasiBarangKeluar'])->name('validasi.barang_keluar');
    Route::get('/validasi/barang-keluar/{reportId}', [LaporanController::class, 'showKeluarDetail'])->name('validasi.keluar_detail');
    Route::post('/validasi-pengajuan-keluar', [LaporanController::class, 'validasiPengajuanKeluar'])->name('validasi.pengajuan.keluar');
    
    // MODUL PEMELIHARAAN AWAL
    Route::get('/pemeliharaan-riwayat', [Pemeliharaan::class, 'pemeliharaanRiwayat'])->name('pemeliharaan.riwayat');
    Route::get('/pemeliharaan-validasi', [Pemeliharaan::class, 'pemeliharaanValidasi'])->name('pemeliharaan.validasi');
    Route::post('/pemeliharaan-validasi/{id}', [Pemeliharaan::class, 'submitValidasi'])->name('pemeliharaan.validasi.submit');
    Route::get('/laporan/{id}', [Pemeliharaan::class, 'getLaporanDetail']);
    // MODUL PEMELIHARAAN AKHIR

    Route::get('/riwayat', [SupervisorDashboardController::class, 'riwayat'])->name('riwayat');
    Route::get('/riwayat-masuk/{reportId}', [LaporanController::class, 'riwayatMasukDetail'])->name('riwayat.detail_masuk');
    Route::get('/riwayat-keluar/{reportId}', [LaporanController::class, 'riwayatKeluarDetail'])->name('riwayat.detail_keluar');
    Route::get('/log-aktivitas', [SupervisorDashboardController::class, 'logAktivitas'])->name('log.aktivitas');
    
});

Route::middleware(['auth', 'role:supervisor_umum'])->prefix('supervisor-umum')->name('supervisor_umum.')->group(function () {
    //
});

Route::middleware(['auth', 'role:staff_gudang'])->prefix('staff-gudang')->name('staff_gudang.')->group(function () {
    Route::get('/dashboard', [StaffGudangDashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring-stok', [StaffGudangDashboardController::class, 'tampil'])->name('monitoring');
    Route::post('/monitoring-stok/update-harga', [StaffGudangDashboardController::class, 'updateHarga'])->name('updateHarga');
    Route::get('/data-barang', [StaffGudangDashboardController::class, 'barangDiterima'])->name('data-barang');
    Route::get('/generate-qrcode/{id}', [StaffGudangDashboardController::class, 'generateQrCode'])->name('generate_qrcode');
    Route::get('/buat-laporan', [StaffGudangDashboardController::class, 'buatLaporan'])->name('buat_laporan');
    Route::post('/kirim-laporan-pengajuan', [LaporanController::class, 'kirimLaporanPengajuan'])->name('kirim_laporan_pengajuan');
    Route::post('/kirim-laporan', [LaporanController::class, 'kirimLaporan'])->name('kirim_laporan');
    Route::get('/form-pengajuan', [LaporanController::class, 'formPengajuan'])->name('form_pengajuan');
    Route::get('/riwayat-aktivitas', [StaffGudangDashboardController::class, 'riwayat'])->name('riwayat.aktivitas');
    // Route::get('/laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
});