<?php
    namespace Marinar\Products;
    use Illuminate\Support\Facades\Artisan;

    use Marinar\Products\Database\Seeders\MarinarProductsInstallSeeder;

    class MarinarProducts {

        public static function getPackageMainDir() {
            return __DIR__;
        }

        public static function injects() {
            return MarinarProductsInstallSeeder::class;
        }

        public static function triggeredInstalled() {
           Artisan::call('db:seed --class="\\\Marinar\\\Products\\\Database\\\Seeders\\\MarinarProductsInstallSeeder"');
           echo Artisan::output();
        }
    }
