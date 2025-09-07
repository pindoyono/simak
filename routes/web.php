<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssessmentExportController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/assessment/export-pdf/{schoolAssessment}', [AssessmentExportController::class, 'exportPDF'])
        ->name('assessment.export-pdf');
});

// Test route
Route::get('/test-export', function () {
    return 'Export route is working!';
});
