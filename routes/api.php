<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController as ApiLoginController;
use App\Http\Controllers\Api\BarangController;
// use App\Http\Controllers\Api\LaporanAPKController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;

Route::middleware(['api'])->group(function(){
    Route::post('/login', [ApiLoginController::class, 'login']);
    Route::post('/login-android', [ApiLoginController::class, 'loginAndroid']);

    // Rute untuk mendapatkan detail barang berdasarkan data QR Code (nomor_identifikasi)
    // Route::get('/barang/{qrCodeData}', [BarangController::class, 'showByQrCode']);
    // Route::get('/barangs', [BarangController::class, 'index']);
    // Route::get('/barang/ringkasan', [BarangController::class, 'ringkasan']);

    // Route untuk authentication tanpa middleware auth
    // Route::post('/login', [ApiLoginController::class, 'login']);
    // Route::post('/register', [RegisterController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ApiLoginController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');

        //API zura
        Route::put('/user/update', [ApiLoginController::class, 'update']);
        // Route::post('/laporan-apk', [LaporanAPKController::class, 'store']);
    });

    Route::get('/form-configs/{form_type}', [SupervisorDashboardController::class, 'getFormConfigsForMobile']);
    Route::get('/master-data/{category_name}', [SupervisorDashboardController::class, 'getMasterDataByCategory']);
    Route::post('/pengajuan-barangs', [SupervisorDashboardController::class, 'pengajuanBarangs']);

    Route::get('/test', function () {
        return response()->json(['message' => 'API Test Berhasil!']);
    });
});
