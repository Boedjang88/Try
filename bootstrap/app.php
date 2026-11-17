<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../app/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRoleScope::class,
            'ensure.role' => \App\Http\Middleware\EnsureRoleScope::class,
            'redirect.role' => \App\Http\Middleware\RedirectByRole::class,
            'academic.window' => \App\Http\Middleware\CheckAcademicWindow::class,
            'financial.clearance' => \App\Http\Middleware\VerifyFinancialClearance::class,
            'active.account' => \App\Http\Middleware\EnsureAccountIsActive::class,
            'role.privilege' => \App\Http\Middleware\EnforcePrivilegeMiddleware::class,
            'privilege' => \App\Http\Middleware\EnforcePrivilegeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
