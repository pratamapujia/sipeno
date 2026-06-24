<?php

use App\Http\Controllers\GeneticScheduleController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\GuruFreeController;
use App\Http\Controllers\GuruMapelController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
// use App\Http\Controllers\RuanganController;
use App\Http\Controllers\SlotJamController;
use App\Http\Controllers\TahunAjaranController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
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
    Route::post('/{id}/activate', [GeneticScheduleController::class, 'activate'])->name('activate');
    Route::delete('/{id}', [GeneticScheduleController::class, 'destroy'])->name('destroy');
});
