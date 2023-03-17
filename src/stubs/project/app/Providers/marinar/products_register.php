<?php

use App\Models\Product;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::model('chProduct', Product::class);
Route::bind('chProductTrashed', function($id) {
    return Product::onlyTrashed()->find($id);
});
Gate::policy(Product::class, ProductPolicy::class);

