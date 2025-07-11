<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController as ApiLoginController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;

Route::post('/login', [ApiLoginController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiLoginController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
});

Route::get('/form-configs/{form_type}', [SupervisorDashboardController::class, 'getFormConfigsForMobile']);
Route::get('/master-data/{category_name}', [SupervisorDashboardController::class, 'getMasterDataByCategory']);

Route::get('/test', function () {
    return response()->json(['message' => 'API Test Berhasil!']);
});