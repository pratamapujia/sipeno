<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneticScheduleController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\GuruFreeController;
use App\Http\Controllers\GuruMapelController;
use App\Http\Controllers\JadwalGuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanJadwalController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\SlotJamController;
use App\Http\Controllers\TahunAjaranController;
use Illuminate\Support\Facades\Route;

// Guest
Route::middleware(['guest'])->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.proses');
});

// Authenticated Role
Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Admin Role
    Route::middleware(['role:admin'])->group(function () {

        Route::get('/admin', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::prefix('admin/m')->name('admin.m.')->group(function () {
            Route::resource('guru', GuruController::class);
            Route::resource('mapel', MapelController::class);
            Route::resource('kelas', KelasController::class);
            Route::resource('thnAjaran', TahunAjaranController::class);
            Route::resource('slotJam', SlotJamController::class);
            // Route::resource('ruangan', RuanganController::class);
            // Import Route
            Route::post('guru/import', [GuruController::class, 'import'])->name('guru.import');
            Route::post('mapel/import', [MapelController::class, 'import'])->name('mapel.import');
            Route::post('kelas/import', [KelasController::class, 'import'])->name('kelas.import');
        });

        Route::prefix('admin')->name('admin.')->group(function () {
            // Penjadwalan
            Route::resource('plotting', GuruMapelController::class)->except(['show']);
            Route::get('guruFree', [GuruFreeController::class, 'index'])->name('guruFree.index');
            Route::get('guruFree/rekap', [GuruFreeController::class, 'rekap'])->name('guruFree.rekap');
            Route::post('guruFree', [GuruFreeController::class, 'store'])->name('guruFree.store');
        });

        Route::prefix('admin/jadwal')->name('admin.jadwal.')->group(function () {
            Route::get('/', [GeneticScheduleController::class, 'index'])->name('index');
            Route::post('/generate', [GeneticScheduleController::class, 'generate'])->name('generate');
            Route::get('/{id}/show', [GeneticScheduleController::class, 'show'])->name('show');
            Route::get('/{id}/print', [GeneticScheduleController::class, 'print'])->name('print');
            Route::get('/print-all/{id}', [GeneticScheduleController::class, 'printAll'])->name('printAll');
            Route::post('/{id}/activate', [GeneticScheduleController::class, 'activate'])->name('activate');
            Route::delete('/{id}', [GeneticScheduleController::class, 'destroy'])->name('destroy');
            Route::put('/update-manual/{id}', [GeneticScheduleController::class, 'updateManual'])->name('updateManual');
        });
    });

    // Guru Role
    Route::middleware(['role:guru'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'guruDashboard'])->name('dashboard');
        Route::get('/jadwal-saya', [JadwalGuruController::class, 'index'])->name('jadwal.saya');
        //Print Route
        Route::get('/jadwal-saya/print', [JadwalGuruController::class, 'print'])->name('jadwal.print');
    });

    // Kepsek Role
    Route::middleware(['role:kepsek'])->prefix('kepsek')->name('kepsek.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kepsekDashboard'])->name('dashboard');
        Route::get('/pemantauan', [LaporanJadwalController::class, 'index'])->name('pemantauan');
    });
});
