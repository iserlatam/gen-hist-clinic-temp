<?php

use App\Http\Controllers\ClinicalRecordPdfController;
use App\Livewire\ClinicalRecords\EditClinicalRecord;
use App\Livewire\ClinicalRecords\ListClinicalRecords;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth'])->prefix('ordenes-medicas')->group(function () {

    Route::get('/', ListClinicalRecords::class)->name('clinical-records.index');

    // "nueva" debe declararse ANTES de /{record} para que no se interprete como id
    Route::get('/nueva', EditClinicalRecord::class)->name('clinical-records.create');

    // {record?} opcional: sin parámetro = nueva orden
    Route::get('/{record?}', EditClinicalRecord::class)->name('clinical-records.edit');

    Route::get('/{record}/pdf/historia-clinica', [ClinicalRecordPdfController::class, 'historia'])
        ->name('clinical-records.pdf.historia');

    Route::get('/{record}/pdf/formula-medica', [ClinicalRecordPdfController::class, 'formula'])
        ->name('clinical-records.pdf.formula');
});

require __DIR__.'/settings.php';
