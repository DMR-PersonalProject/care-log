<?php

use App\Http\Controllers\CareEntryController;
use App\Http\Controllers\DailyLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrintReportController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::get('/log', DailyLogController::class)->name('log');

Route::resource('entries', CareEntryController::class)->except(['create']);

Route::get('/reports', ReportController::class)->name('reports');
Route::get('/reports/print', PrintReportController::class)->name('reports.print');
