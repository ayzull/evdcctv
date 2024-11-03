<?php

use App\Http\Controllers\AnprEventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CCTVController;

// Route::get('/', [CCTVController::class, 'index'])->name('cctv.index');

// Route::get('/camera/add', [CCTVController::class, 'create'])->name('components.camera');

// Route::post('/add', [CCTVController::class, 'add'])->name('cameras.add');

// Route::get('/cctv/stream', [CCTVController::class, 'stream'])->name('cctv.streams');

Route::get('/anpr-events', [AnprEventController::class, 'index'])->name('cctv.index');

// Route::get('/cctv/show', [CCTVController::class, 'show'])->name('cctv.show');

// Route::delete('/{camera}' , 'destroy')->name('cameras.destroy');

Route::controller(CCTVController::class)
    // ->prefix('cctv')
    ->name('cctv.')
    ->group(function () {

        Route::get('/', 'index')->name('index');

        Route::post('/add', 'add')->name('add');

        Route::get('/{camera}', 'show')->name('show');

        Route::delete('/{camera}', 'delete')->name('delete');

        Route::get('/{camera}/edit', 'edit')->name('edit');

        Route::patch('/{camera}', 'update')->name('update');
    });
