<?php
    namespace Marinar\Products\Database\Seeders;

    use Illuminate\Database\Seeder;
    use Marinar\Products\MarinarProducts;

    class MarinarProductsInstallSeeder extends Seeder {

        use \Marinar\Marinar\Traits\MarinarSeedersTrait;

        public static function configure() {
            static::$packageName = 'marinar_products';
            static::$packageDir = MarinarProducts::getPackageMainDir();
        }

        public function run() {
            if(!in_array(env('APP_ENV'), ['dev', 'local'])) return;

            $this->autoInstall();

            $this->refComponents->info("Done!");
        }
    }
