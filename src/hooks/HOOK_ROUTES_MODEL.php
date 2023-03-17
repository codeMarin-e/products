<?php
Route::group([
    'controller' => \App\Http\Controllers\Admin\ProductController::class,
    'middleware' => ['auth:admin', 'can:view,'.\App\Models\Product::class],
    'as' => 'products.', //naming prefix
    'prefix' => '{chCategory}/products', //for routes
], function() {
    Route::get('', 'index')->name('index');
    Route::post('', 'store')->name('store')->middleware('can:create,' . \App\Models\Product::class);
    Route::get('create', 'create')->name('create')->middleware('can:create,' . \App\Models\Product::class);
    Route::get('{chProduct}/edit', 'edit')->name('edit');
    Route::get('{chProduct}', 'edit')->name('show');
    Route::get('{chProduct}/move/{direction}', "move")->name('move')->middleware('can:update,chProduct');
    Route::get('{chProductTrashed}/restore', "restore")->name('restore')->middleware('can:update,chProduct');
    Route::patch('{chProduct}', 'update')->name('update')->middleware('can:update,chProduct');
    Route::delete('{chProduct}', 'destroy')->name('destroy')->middleware('can:delete,chProduct');
});
