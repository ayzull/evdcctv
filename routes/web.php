<?php

use App\Http\Controllers\AnprEventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CCTVController;

Route::get('/', [CCTVController::class, 'index'])->name('cctv.index');

Route::get('/camera/add', [CCTVController::class, 'create'])->name('components.camera');

Route::post('/add', [CCTVController::class, 'add'])->name('cameras.add');

Route::get('/cctv/stream', [CCTVController::class, 'stream'])->name('cctv.streams');

Route::get('/anpr-events', [AnprEventController::class, 'index'])->name('anpr.index');
