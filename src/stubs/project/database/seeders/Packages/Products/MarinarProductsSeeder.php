<?php
namespace Database\Seeders\Packages\Products;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class MarinarProductsSeeder extends Seeder {

    public function run() {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        Permission::upsert([
            ['guard_name' => 'admin', 'name' => 'products.view'],
            ['guard_name' => 'admin', 'name' => 'product.create'],
            ['guard_name' => 'admin', 'name' => 'product.update'],
            ['guard_name' => 'admin', 'name' => 'product.delete'],
        ], ['guard_name','name']);
    }
}
