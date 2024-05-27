<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AutoCrudController;

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

    // Rutas específicas con parámetros múltiples primero
    Route::post('/{model}/load-items', [AutoCrudController::class, 'loadItems'])->name('dashboard.model.load-items');
    Route::post('/{model}/{id}', [AutoCrudController::class, 'update'])->name('dashboard.model.update');
    Route::delete('/{model}/{id}', [AutoCrudController::class, 'destroy'])->name('dashboard.model.destroy');
    Route::delete('/{model}/{id}/permanent', [AutoCrudController::class, 'destroyPermanent'])->name('dashboard.model.destroyPermanent');
    Route::post('/{model}/{id}/restore', [AutoCrudController::class, 'restore'])->name('dashboard.model.restore');

    // Luego rutas de acciones específicas que incluyen el parámetro de modelo
    Route::get('/{model}/export-excel', [AutoCrudController::class, 'exportExcel'])->name('dashboard.model.exportExcel');

    // Rutas para operaciones básicas de CRUD al final
    Route::get('/{model}', [AutoCrudController::class, 'index'])->name('dashboard.model.index');
    Route::get('/{model}/all', [AutoCrudController::class, 'getAll'])->name('dashboard.model.all');
    Route::post('/{model}', [AutoCrudController::class, 'store'])->name('dashboard.model.store');

    //Rutas para vincular y desvincular relaciones N:M
    Route::post('/{model}/{id}/bind/{externalRelation}/{item}', [AutoCrudController::class, 'bind'])->name('dashboard.model.bind');
    Route::post('/{model}/{id}/unbind/{externalRelation}/{item}', [AutoCrudController::class, 'unbind'])->name('dashboard.model.unbind');
    
});

require __DIR__ . '/auth.php';
