<?php

use App\Http\Controllers\CareEntryController;
use App\Http\Controllers\DailyLogController;
use Illuminate\Support\Facades\Route;

// Becomes the Dashboard in Milestone 4.
Route::redirect('/', '/log')->name('home');

Route::get('/log', DailyLogController::class)->name('log');

Route::resource('entries', CareEntryController::class)->except(['index', 'create']);
