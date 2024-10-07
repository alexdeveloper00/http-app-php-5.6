<?php 
require __DIR__ . '/vendor/autoload.php';

spl_autoload_register(function ($class) {
    $base_dir = __DIR__ . '/';

    $file = $base_dir . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_DEPRECATED);

use Http\HttpApplication;

$config = require __DIR__ . '/config.php';
$app = new HttpApplication;
return $app->run();