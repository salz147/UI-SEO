<?php

use App\Http\Controllers\SEOController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SEO Routes
|--------------------------------------------------------------------------
|
| Routes for SEO Management Module
|
*/

// SEO CRUD Resources
Route::resource('seo', SEOController::class)->except(['show']);
Route::redirect('/seo/dashboard', '/seo')->name('seo.dashboard');
Route::get('/logging', [SEOController::class, 'logging'])->name('seo.logging');

// SEO Period Management
Route::get('/seo-period-edit/{period}', [SEOController::class, 'editSEOPeriod'])->name('seo.period.edit');
Route::post('/seo/{seo}/periods', [SEOController::class, 'storeSEOPeriod'])->name('seo.period.store');
Route::put('/update-seo-period/{period}', [SEOController::class, 'updateSEOPeriod'])->name('seo.period.update');
Route::delete('/delete-seo-period/{period}', [SEOController::class, 'deleteSEOPeriod'])->name('seo.period.delete');

// Mark period as paid
Route::post('/mark-period-paid/{period}', [SEOController::class, 'markPeriodAsPaid'])->name('seo.period.paid');

// View SEO items
Route::get('/view-seo-items/{period}', [SEOController::class, 'viewSEOItems'])->name('seo.items.view');

// Store SEO items (via AJAX)
Route::put('/submit-media-seo', [SEOController::class, 'storeSEOItems'])->name('seo.items.store');
Route::post('/upload-seo-report/{period}', [SEOController::class, 'uploadReportMedia'])->name('seo.report.upload');
Route::post('/send-seo-report/{period}', [SEOController::class, 'sendReport'])->name('seo.report.send');

// Get SEO items (AJAX)
Route::get('/get-seo-items/{period}', [SEOController::class, 'getSEOItems'])->name('seo.items.get');

// Delete SEO item
Route::post('/delete-seo-item/{item}', [SEOController::class, 'deleteSEOItem'])->name('seo.item.delete');
Route::post('/hide-seo-local/{seo}', [SEOController::class, 'hideLocal'])->name('seo.local.hide');
Route::get('/get-conv-from-marketing/{marketing}', [SEOController::class, 'getConversationsByMarketing'])->name('seo.conversations.by-marketing');

// List view
Route::get('/seo', [SEOController::class, 'index'])->name('seo.index');

// Public API Routes (untuk integration dengan sistem lain)
Route::get('/get-active-period-seo/{seo}', [SEOController::class, 'getActivePeriodSEO'])->name('seo.active.period');
Route::get('/seo/statistics', [SEOController::class, 'statistics'])->name('seo.statistics');
