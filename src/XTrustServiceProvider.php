<?php namespace XdroidTeam\XTrust;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class XTrustServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Register blade directives
        $this->bladeDirectives();

        $this->publishes([
            __DIR__.'/../database/migrations' => base_path('database/migrations'),
        ], 'xdroidteam-xtrust');

        $this->publishes([
            __DIR__.'/../database/migrations' => base_path('database/migrations'),
            __DIR__.'/../config/xdroidteam-xtrust.php' => config_path('xdroidteam-xtrust.php'),

        ], 'xdroidteam-xtrust');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/xdroidteam-xtrust.php', 'xdroidteam-xtrust'
        );

        // Add route middleware
        $this->app['router']->aliasMiddleware('permission', config('xdroidteam-xtrust.route_middleware'));
    }

    private function bladeDirectives()
    {
        \Blade::if('permission', function($expression, Authenticatable $user = null) {
            return XTrust::hasPermission($expression, $user);
        });

        \Blade::if('permissions', function($expression, Authenticatable $user = null) {
            return XTrust::hasPermissions($expression, $user);
        });

        \Blade::if('oneofpermissions', function($expression, Authenticatable $user = null) {
            return XTrust::hasOneOfPermissions($expression, $user);
        });
    }
}
