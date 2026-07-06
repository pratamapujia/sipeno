<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CetakJadwalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GeneticScheduleController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\GuruFreeController;
use App\Http\Controllers\GuruMapelController;
use App\Http\Controllers\GuruPiketController;
use App\Http\Controllers\JadwalGuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanJadwalController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\ProfileController;
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

        // Data Master
        Route::prefix('admin/m')->name('admin.m.')->group(function () {
            Route::resource('guru', GuruController::class);
            Route::resource('mapel', MapelController::class);
            Route::resource('kelas', KelasController::class);
            Route::resource('thnAjaran', TahunAjaranController::class);
            Route::resource('slotJam', SlotJamController::class);
            // Route::resource('ruangan', RuanganController::class);

            // Import Route
            Route::post('/guru/import', [GuruController::class, 'import'])->name('guru.import');
            Route::post('/mapel/import', [MapelController::class, 'import'])->name('mapel.import');
            Route::post('/kelas/import', [KelasController::class, 'import'])->name('kelas.import');
            Route::post('/slotJam/import', [SlotJamController::class, 'import'])->name('slotJam.import');

            // Switch Status Tahun Ajaran
            Route::patch('thnAjaran/{id}/toggle-status', [TahunAjaranController::class, 'toggleStatus'])->name('thnAjaran.toggleStatus');
        });

        // Plotting Guru
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::resource('plotting', GuruMapelController::class)->except(['show']);
            Route::get('/guruFree', [GuruFreeController::class, 'index'])->name('guruFree.index');
            Route::get('/guruFree/rekap', [GuruFreeController::class, 'rekap'])->name('guruFree.rekap');
            Route::post('/guruFree', [GuruFreeController::class, 'store'])->name('guruFree.store');
        });

        // Jadwal Guru
        Route::prefix('admin/jadwal')->name('admin.jadwal.')->group(function () {
            Route::get('/index', [GeneticScheduleController::class, 'index'])->name('index');
            Route::post('/generate', [GeneticScheduleController::class, 'generate'])->name('generate');
            Route::get('/{id}/show', [GeneticScheduleController::class, 'show'])->name('show');
            Route::get('/{id}/print', [GeneticScheduleController::class, 'print'])->name('print');
            Route::get('/print-all/{id}', [GeneticScheduleController::class, 'printAll'])->name('printAll');
            Route::post('/{id}/activate', [GeneticScheduleController::class, 'activate'])->name('activate');
            Route::delete('{id}', [GeneticScheduleController::class, 'destroy'])->name('destroy');
            Route::put('/update-manual/{id}', [GeneticScheduleController::class, 'updateManual'])->name('updateManual');
        });

        // Cetak Jadwal
        Route::prefix('admin/cetak')->name('admin.cetak.')->group(function () {
            Route::get('/index', [CetakJadwalController::class, 'index'])->name('index');
            Route::get('/guru', [CetakJadwalController::class, 'printGuru'])->name('guru');
            Route::get('/kelas', [CetakJadwalController::class, 'printKelas'])->name('kelas');
            Route::get('/semua', [CetakJadwalController::class, 'printAll'])->name('semua');
        });

        // Guru Piket
        Route::prefix('admin/piket')->name('admin.piket.')->group(function () {
            Route::get('/index', [GuruPiketController::class, 'index'])->name('index');
            Route::post('/store', [GuruPiketController::class, 'store'])->name('store');
            Route::put('/{id}', [GuruPiketController::class, 'update'])->name('update');
            Route::delete('/{id}', [GuruPiketController::class, 'destroy'])->name('destroy');
            Route::get('/cetak', [GuruPiketController::class, 'print'])->name('print');
        });
    });

    // Guru Role
    Route::middleware(['role:guru'])->prefix('guru')->name('guru.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'guruDashboard'])->name('dashboard');
        Route::get('/jadwal-saya', [JadwalGuruController::class, 'index'])->name('jadwal.saya');
        Route::get('/jadwal-saya/print', [JadwalGuruController::class, 'print'])->name('jadwal.print');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile/password', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Kepsek Role
    Route::middleware(['role:kepsek'])->prefix('kepsek')->name('kepsek.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'kepsekDashboard'])->name('dashboard');
        Route::get('/pemantauan', [LaporanJadwalController::class, 'index'])->name('pemantauan');
    });
});
