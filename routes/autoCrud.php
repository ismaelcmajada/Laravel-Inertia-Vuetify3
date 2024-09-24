<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AutoCrud\AutoCrudController;
use App\Http\Controllers\AutoCrud\AutoTableController;
use App\Http\Controllers\AutoCrud\AutoCompleteController;
use App\Http\Controllers\AutoCrud\SessionController;
use App\Http\Controllers\AutoCrud\ImageController;
use App\Http\Controllers\AutoCrud\FileController;

Route::get('/dashboard/public/images/{model}/{field}/{id}', [ImageController::class, 'publicImage']);
Route::get('/dashboard/public/files/{model}/{field}/{id}', [FileController::class, 'publicFile']);


Route::middleware(['auth', 'checkForbiddenActions'])->prefix('dashboard')->group(function () {

    Route::get('/private/images/{model}/{field}/{id}', [ImageController::class, 'privateImage']);
    Route::get('/private/files/{model}/{field}/{id}', [FileController::class, 'privateFile']);

    Route::post('/session/setSession', [SessionController::class, 'setSession'])->name('dashboard.session.setSession');

    Route::post('/{model}/load-autocomplete-items', [AutoCompleteController::class, 'loadAutocompleteItems'])->name('dashboard.model.load-autocomplete-items');
    Route::post('/{model}/load-items', [AutoTableController::class, 'loadItems'])->name('dashboard.model.load-items');
    Route::post('/{model}/{id}', [AutoCrudController::class, 'update'])->name('dashboard.model.update');
    Route::post('/{model}/{id}/destroy', [AutoCrudController::class, 'destroy'])->name('dashboard.model.destroy');
    Route::post('/{model}/{id}/permanent', [AutoCrudController::class, 'destroyPermanent'])->name('dashboard.model.destroyPermanent');
    Route::post('/{model}/{id}/restore', [AutoCrudController::class, 'restore'])->name('dashboard.model.restore');

    Route::get('/{model}/export-excel', [AutoCrudController::class, 'exportExcel'])->name('dashboard.model.exportExcel');

    Route::get('/{model}', [AutoCrudController::class, 'index'])->name('dashboard.model.index');
    Route::get('/{model}/all', [AutoCompleteController::class, 'getAll'])->name('dashboard.model.all');
    Route::post('/{model}', [AutoCrudController::class, 'store'])->name('dashboard.model.store');

    Route::post('/{model}/{id}/pivot/{externalRelation}/{item}', [AutoCrudController::class, 'updatePivot'])->name('dashboard.model.updatePivot');
    Route::post('/{model}/{id}/bind/{externalRelation}/{item}', [AutoCrudController::class, 'bind'])->name('dashboard.model.bind');
    Route::post('/{model}/{id}/unbind/{externalRelation}/{item}', [AutoCrudController::class, 'unbind'])->name('dashboard.model.unbind');
});
