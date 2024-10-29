<?php

use App\Http\Controllers\AnprEventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CCTVController;

Route::get('/', [CCTVController::class, 'index'])->name('cctv.index');

Route::get('/nothing', function () {
    return view('');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/anpr-events', [AnprEventController::class, 'index'])->name('anpr.index');
//Route::get('/test', [CCTVController::class, 'index'])->name('cctv.index');