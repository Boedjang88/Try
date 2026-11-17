<?php

require __DIR__ . '/../vendor/autoload.php';

// Guarantee local autoloader precedence for App, Database, and Tests namespaces
spl_autoload_register(function ($class) {
    $baseDir = dirname(__DIR__);
    if (str_starts_with($class, 'App\\')) {
        $file = $baseDir . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    if (str_starts_with($class, 'Database\\Seeders\\')) {
        $file = $baseDir . '/database/seeders/' . str_replace('\\', '/', substr($class, 17)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    if (str_starts_with($class, 'Tests\\')) {
        $file = $baseDir . '/tests/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    return false;
}, true, true);
