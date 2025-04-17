<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\KasraController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\JadwalKegiatanController;


// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/storage/pengumuman/{filename}', [PengumumanController::class, 'serveFile'])
    ->where('filename', '.*');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // User management routes (Admin only)
    Route::middleware(['ability:admin'])->group(function () {
        Route::apiResource('users', UserController::class);
    });
    
    // Mahasiswa resource routes
    Route::apiResource('mahasiswa', MahasiswaController::class);
    Route::post('mahasiswa/import', [MahasiswaController::class, 'import']);
    Route::get('mahasiswa/kamar-options', [MahasiswaController::class, 'getKamarOptions']);

    Route::apiResource('kasra', KasraController::class);
    
    Route::apiResource('kamar', KamarController::class);
    Route::get('kamar-available/{gender}', [KamarController::class, 'getAvailableRooms']);

    Route::apiResource('pelanggaran', PelanggaranController::class);
    Route::get('pelanggaran/mahasiswa/{nim}', [PelanggaranController::class, 'getByNim']);

    Route::apiResource('pembayaran', PembayaranController::class);
    Route::get('pembayaran/mahasiswa/{nim}', [PembayaranController::class, 'getByNim']);

    Route::apiResource('pengaduan', PengaduanController::class);
    Route::get('pengaduan/mahasiswa/{nim}', [PengaduanController::class, 'getByNim']);

    Route::apiResource('pengumuman', PengumumanController::class);
    Route::get('pengumuman/{id}/download', [PengumumanController::class, 'downloadFile']);
    
    Route::apiResource('jadwal-kegiatan', JadwalKegiatanController::class);
    Route::get('jadwal-kegiatan/{id}/download', [JadwalKegiatanController::class, 'downloadFile']);
    Route::get('jadwal-kegiatan-upcoming', [JadwalKegiatanController::class, 'upcoming']);
});