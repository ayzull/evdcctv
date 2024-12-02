
<?php
// routes\web.php
use App\Http\Controllers\AnprEventController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CCTVController;

Route::get('/anpr-events', [AnprEventController::class, 'index'])->name('anpr.index');
Route::get('/anpr-dahua-events', [AnprEventController::class, 'dahuaIndex'])->name('anpr.dahua_anpr');

Route::get('/analytics/fetch-data', [AnprEventController::class, 'fetchAnalyticsData']);

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
