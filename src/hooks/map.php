<?php
return [
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'resources', 'views', 'components', 'admin', 'box_sidebar.blade.php']) => [
        "{{--  @HOOK_ADMIN_SIDEBAR  --}}" => implode(DIRECTORY_SEPARATOR, [__DIR__, 'HOOK_ADMIN_SIDEBAR.blade.php']),
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'config', 'marinar.php']) => [
        "// @HOOK_MARINAR_CONFIG_ADDONS" => "\t\t\\Marinar\\Products\\MarinarProducts::class, \n"
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'app', 'Models', 'Category.php']) => [
        "// @HOOK_TRAITS" => "\tuse \\App\\Traits\\CategoryProductTrait; \n",
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'routes', 'admin',  'categories.php']) => [
        "// @HOOK_ROUTES_MODEL" => implode(DIRECTORY_SEPARATOR, [__DIR__, 'HOOK_ROUTES_MODEL.php']),
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'app', 'Http', 'Controllers', 'Admin', 'CategoryController.php']) => [
        "// @HOOK_INDEX_END" => '$subBldQry->with("products");',
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'resources', 'views', 'admin', 'categories', 'categories.blade.php']) => [
        "{{-- @HOOK_AFTER_NAME_TH --}}" => implode(DIRECTORY_SEPARATOR, [__DIR__, 'HOOK_AFTER_NAME_TH.blade.php']),
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'resources', 'views', 'admin', 'categories', 'categories_list.blade.php']) => [
        "{{-- @HOOK_AFTER_NAME --}}" => implode(DIRECTORY_SEPARATOR, [__DIR__, 'HOOK_AFTER_NAME.blade.php']),
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'app', 'Models', 'Uri.php']) => [
        "// @HOOK_URIABLE_CLASSES" => "\t\t\App\Models\Product::class => 'admin/products/product.uri_type', \n",
    ],
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'config','marinar_categories.php']) => [
        "// @HOOK_CONFIGS_ADDONS" => "\t\t\\Marinar\\Products\\MarinarProducts::class, \n",
        "// @HOOK_CONFIG_EXCLUDE_INJECTS" => implode(DIRECTORY_SEPARATOR, [__DIR__, 'HOOK_CONFIG_EXCLUDE_INJECTS.php_tpl']),
    ]
];
