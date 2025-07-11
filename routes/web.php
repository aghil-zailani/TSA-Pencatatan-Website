<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController as WebLoginController; // Alias untuk kejelasan
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\StaffGudang\DashboardController as StaffGudangDashboardController;

// Route untuk login WEB
Route::get('/login', [WebLoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [WebLoginController::class, 'login'])->middleware('guest'); // Akan ditangani oleh WebLoginController
Route::post('/logout', [WebLoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring-stok', [SupervisorDashboardController::class, 'tampil'])->name('monitoring');
    Route::post('/monitoring-stok/update-harga', [SupervisorDashboardController::class, 'updateHarga'])->name('updateHarga');
    Route::get('/exportcsv', [SupervisorDashboardController::class, 'exportExcel'])->name('exportExcel');
    Route::get('/export-pdf', [SupervisorDashboardController::class, 'exportPdf'])->name('exportPdf');
    Route::get('/master-data', [SupervisorDashboardController::class, 'manageMasterData'])->name('master.data');
    Route::get('/master-data/{form_config_category}', [SupervisorDashboardController::class, 'manageSpecificMaster'])->name('master.data.specific');
    Route::post('/master-data-store', [SupervisorDashboardController::class, 'storeMasterData'])->name('master.data.store');
    Route::put('/master-data/{masterData}', [SupervisorDashboardController::class, 'updateMasterData'])->name('master.data.update');
    Route::delete('/master-data/{masterData}', [SupervisorDashboardController::class, 'destroyMasterData'])->name('master.data.destroy');
    Route::delete('/master-data/category/{category_name}', [SupervisorDashboardController::class, 'destroyMasterCategory'])->name('master.data.destroy_category');
    Route::get('/api/master-data/{category}', [SupervisorDashboardController::class, 'getMasterDataByCategory']);
    Route::delete('/master-data/category/{category_name}', [SupervisorDashboardController::class, 'destroyMasterCategory'])->name('master.data.destroy_category');
    Route::get('/validasi-barang-masuk', [SupervisorDashboardController::class, 'validasiBarangMasuk'])->name('validasi.barang_masuk'); // Halaman Supervisor
    Route::get('/validasi-laporan/{reportId}', [SupervisorDashboardController::class, 'lihatDetailLaporan'])->name('validasi.laporan_detail');
    Route::get('/validasi/barang-keluar', [SupervisorDashboardController::class, 'validasiBarangKeluar'])->name('validasi.barang_keluar');

    Route::get('/validasi/barang-keluar/{id}', [SupervisorDashboardController::class, 'showKeluarDetail'])->name('validasi.keluar.detail');

    Route::post('/validasi/barang-keluar/{id}/detail', [SupervisorDashboardController::class, 'validasiKeluar'])->name('validasi.keluar.validasi');
    Route::post('/validasi/barang-keluar/{id}/terima', [SupervisorDashboardController::class, 'terimaKeluar'])->name('validasi.keluar.terima');
    Route::post('/validasi/barang-keluar/{id}/tolak', [SupervisorDashboardController::class, 'tolakKeluar'])->name('validasi.keluar.tolak');

    Route::post('/validasi-pengajuan', [SupervisorDashboardController::class, 'validasiPengajuan'])->name('validasi.pengajuan');
    Route::get('/pemeliharaan', [SupervisorDashboardController::class, 'pemeliharaan'])->name('pemeliharaan');
    Route::get('/riwayat', [SupervisorDashboardController::class, 'riwayat'])->name('riwayat');
    Route::get('/log-aktivitas', [SupervisorDashboardController::class, 'logAktivitas'])->name('log.aktivitas');
});

Route::middleware(['auth', 'role:staff_gudang'])->prefix('staff-gudang')->name('staff_gudang.')->group(function () {
    Route::get('/dashboard', [StaffGudangDashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring-stok', [StaffGudangDashboardController::class, 'tampil'])->name('monitoring');
    Route::post('/monitoring-stok/update-harga', [StaffGudangDashboardController::class, 'updateHarga'])->name('updateHarga');
    Route::get('/data-barang', [StaffGudangDashboardController::class, 'barangDiterima'])->name('data-barang');
    Route::get('/generate-qrcode/{id}', [StaffGudangDashboardController::class, 'generateQrCode'])->name('generate_qrcode');
    Route::get('/buat-laporan', [StaffGudangDashboardController::class, 'buatLaporan'])->name('buat_laporan');
    Route::post('/kirim-laporan', [StaffGudangDashboardController::class, 'kirimLaporan'])->name('kirim_laporan');
    Route::get('/form-pengajuan', [StaffGudangDashboardController::class, 'formPengajuan'])->name('form_pengajuan');
    Route::get('/riwayat-aktivitas', [StaffGudangDashboardController::class, 'riwayat'])->name('riwayat.aktivitas');
    // Route::get('/laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
});