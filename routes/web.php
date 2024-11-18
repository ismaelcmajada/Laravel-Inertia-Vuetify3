<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Ismaelcmajada\LaravelAutoCrud\Http\Controllers\AutoCrudController;

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


Route::middleware(['auth', 'checkForbiddenActions'])->prefix('dashboard')->group(function () {

    //Routes must have the following structure to work with the dialogs:
    // index: /item
    // store: /item
    // update: /item/{id}
    // destroy: /item/{id}
    // destroyPermanent: /item/{id}/permanent
    // restore: /item/{id}/restore
    // exportExcel: /item/export-excel

    Route::get('/calendar-example', function () {
        return Inertia::render('Dashboard/CalendarExpample');
    })->name('dashboard.calendar-example');

    Route::get('/{model}', [AutoCrudController::class, 'index'])->name('laravel-auto-crud.model.index');
});

require __DIR__ . '/auth.php';
