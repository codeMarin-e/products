<?php
    namespace Marinar\Products\Database\Seeders;

    use App\Models\Product;
    use Illuminate\Database\Seeder;
    use Marinar\Products\MarinarProducts;
    use Spatie\Permission\Models\Permission;

    class MarinarProductsRemoveSeeder extends Seeder {

        use \Marinar\Marinar\Traits\MarinarSeedersTrait;

        public static function configure() {
            static::$packageName = 'marinar_products';
            static::$packageDir = MarinarProducts::getPackageMainDir();
        }

        public function run() {
            if(!in_array(env('APP_ENV'), ['dev', 'local'])) return;

            $this->autoRemove();

            $this->refComponents->info("Done!");
        }

        public function clearMe() {
            $this->refComponents->task("Clear DB", function() {
                foreach(Product::get() as $product) {
                    $product->delete();
                }
                Permission::whereIn('name', [
                    'products.view',
                    'product.create',
                    'product.update',
                    'product.delete',
                ])
                ->where('guard_name', 'admin')
                ->delete();
                app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
                return true;
            });
        }
    }
