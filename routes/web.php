<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\SuscriptorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('auth')->prefix('dashboard')->group(function () {

    //Routes must have the following structure to work with the dialogs:
    // index: /item
    // store: /item
    // update: /item/{id}
    // destroy: /item/{id}
    // destroyPermanent: /item/{id}/permanent
    // restore: /item/{id}/restore
    // exportExcel: /item/export-excel


    //Suscriptor
    Route::get('/suscriptores', [SuscriptorController::class, 'index'])->name('dashboard.suscriptores');
    Route::post('/suscriptores/load-items', [SuscriptorController::class, 'loadItems'])->name('dashboard.suscriptores.load-items');
    Route::post('/suscriptores', [SuscriptorController::class, 'store'])->name('dashboard.suscriptores.store');
    Route::put('/suscriptores/{subscriber}', [SuscriptorController::class, 'update'])->name('dashboard.suscriptores.update');
    Route::delete('/suscriptores/{subscriber}', [SuscriptorController::class, 'destroy'])->name('dashboard.suscriptores.destroy');
    Route::delete('/suscriptores/{subscriber}/permanent', [SuscriptorController::class, 'destroyPermanent'])->name('dashboard.suscriptores.destroyPermanent');
    Route::post('/suscriptores/{id}/restore', [SuscriptorController::class, 'restore'])->name('dashboard.suscriptores.restore');
    Route::get('/suscriptores/export-excel', [SuscriptorController::class, 'exportExcel'])->name('dashboard.suscriptores.exportExcel');
});

require __DIR__ . '/auth.php';
