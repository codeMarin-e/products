<?php
	return [
		'install' => [
            'php artisan db:seed --class="\Marinar\Products\Database\Seeders\MarinarProductsInstallSeeder"',
		],
		'remove' => [
            'php artisan db:seed --class="\Marinar\Products\Database\Seeders\MarinarProductsRemoveSeeder"',
        ]
	];
