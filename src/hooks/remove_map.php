<?php
return [
    implode(DIRECTORY_SEPARATOR, [ base_path(), 'resources', 'views', 'components', 'admin', 'box_sidebar.blade.php']) => [
        "{{--  @REMOVE_SIDEBAR_CATEGORIES  --}}" => implode(DIRECTORY_SEPARATOR, [__DIR__, 'REMOVE_SIDEBAR_CATEGORIES.blade.php']),
    ],
];
