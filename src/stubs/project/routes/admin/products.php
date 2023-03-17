<?php

use App\Http\Controllers\Admin\ProductsController;
use App\Models\Product;

Route::group([
    'middleware' => ['auth:admin', 'can:view,'.Product::class],
    'as' => 'products.', //naming prefix
    'prefix' => 'products', //for routes
], function() {
    Route::group([
        'controller' => ProductsController::class,
    ], function() {
        Route::get('', 'index')->name('index');

        // @HOOK_PRODUCTS_ROUTES
    });

    // @HOOK_ROUTES
});

// @HOOK_ADDON_ROUTES
