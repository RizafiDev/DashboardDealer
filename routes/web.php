<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use Illuminate\Support\Facades\Auth;

// Authentication Routes untuk Absensi (menggunakan guard karyawan)
Route::get('/login', [LoginController::class, 'show'])->name('login')->middleware('guest:karyawan'); // Hanya guest karyawan
Route::post('/login', [LoginController::class, 'authenticate'])->middleware('guest:karyawan');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth:karyawan'); // Hanya auth karyawan

// Protected Routes untuk Absensi (menggunakan guard karyawan)
Route::middleware(['auth:karyawan'])->prefix('absensi')->name('absensi.')->group(function () {
    Route::get('/', [AbsensiController::class, 'index'])->name('index');
    Route::post('/absen', [AbsensiController::class, 'absen'])->name('absen');
    Route::get('/histori', [AbsensiController::class, 'histori'])->name('histori');

    // Rute baru untuk pengajuan cuti/izin
    Route::post('/ajukan-cuti', [AbsensiController::class, 'ajukanCuti'])->name('ajukan-cuti');
});

// Route fallback jika pengguna sudah login (karyawan) mencoba akses /login
Route::get('/', function () {
    if (Auth::guard('karyawan')->check()) {
        return redirect()->route('absensi.index');
    }
    return redirect()->route('login');
})->name('home');
