<?php

namespace Modules\TitanGo\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected string $moduleNamespace = 'Modules\\TitanGo\\Http\\Controllers';

    public function boot(): void
    {
        parent::boot();

        // Register module middleware alias
        app('router')->aliasMiddleware('titango.entitled', \Modules\TitanGo\Http\Middleware\RequireTitanGoEntitlement::class);
    }

    public function map(): void
    {
        Route::middleware('web')
            ->namespace($this->moduleNamespace)
            ->group(module_path('TitanGo', '/Routes/web.php'));
    }
}
