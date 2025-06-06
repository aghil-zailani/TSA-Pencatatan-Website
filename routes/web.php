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
});

Route::middleware(['auth', 'role:staff_gudang'])->prefix('staff-gudang')->name('staff_gudang.')->group(function () {
    Route::get('/dashboard', [StaffGudangDashboardController::class, 'index'])->name('dashboard');
});