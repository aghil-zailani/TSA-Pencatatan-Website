<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController as ApiLoginController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\LaporanAPKController;
use App\Http\Controllers\Api\NotifikasiController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Supervisor\MasterDataController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\StaffGudang\DashboardController as StaffDashboardController;

Route::middleware(['api'])->group(function(){
    Route::post('/login', [ApiLoginController::class, 'login']);

    // Punya Zura
    Route::post('/login-android', [ApiLoginController::class, 'loginAndroid']);
    Route::post('/register', [RegisterController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');

        // 🔐 STAFF GUDANG
        Route::middleware('role:staff_gudang')->prefix('staff')->group(function () {
            Route::get('/barang/ringkasan', [BarangController::class, 'ringkasan']);
            Route::get('/barang/{qrCodeData}', [BarangController::class, 'showByQrCode']);
            Route::get('/barangs', [BarangController::class, 'index']);
            Route::put('/user/update', [ApiLoginController::class, 'update']);
            Route::post('/laporan-apk', [LaporanAPKController::class, 'store']);
            Route::get('/notifikasi', [NotifikasiController::class, 'index']);
            Route::post('/notifikasi/generate', [NotifikasiController::class, 'generateNotifikasi']);
        });

        // 🔐 INSPEKTOR
        Route::middleware('role:inspektor')->prefix('inspektor')->group(function () {
            //
        });

        // 🔐 Supervisor Umum
        Route::middleware('role:supervisor_umum')->prefix('supervisor')->group(function () {
            //
        });

        // ✅ Logout dan info user
        Route::post('/logout', [ApiLoginController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        Route::post('/pengajuan-barangs', [StaffDashboardController::class, 'pengajuanBarangs']);
    });

    Route::get('/form-configs/{form_type}', [MasterDataController::class, 'getFormConfigsForMobile']);
    Route::get('/master-data/{category_name}', [MasterDataController::class, 'getMasterDataByCategory']);
    Route::post('/transaksi/barang-keluar', [StaffDashboardController::class, 'catatBarangKeluar']);

    Route::get('/test', function () {
        return response()->json(['message' => 'API Test Berhasil!']);
    });
});
