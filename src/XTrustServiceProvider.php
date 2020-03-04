<?php namespace XdroidTeam\XTrust;

use Illuminate\Support\ServiceProvider;
use XdroidTeam\XTrust\Middleware\XTrustPermissionMiddleware;

class XTrustServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Register blade directives
        $this->bladeDirectives();

        $this->publishes([
            __DIR__.'/../database/migrations' => base_path('database/migrations'),
        ], 'xdroidteam-xtrust');
    }

    public function register()
    {
        // Add route middleware
        $this->app['router']->aliasMiddleware('permission', XTrustPermissionMiddleware::class);
    }

    private function bladeDirectives()
    {
            // Laravel 5.5 compatibility
            \Blade::if('permission', function($expression) {
                return XTrust::hasPermission($expression);
            });

            \Blade::if('permissions', function($expression) {
                return XTrust::hasPermissions($expression);
            });

            \Blade::if('oneofpermissions', function($expression) {
                return XTrust::hasOneOfPermissions($expression);
            });

            // Laravel 5.5> compatibility
            /*
            \Blade::directive('permission', function($expression) {
                return "<?php if (\\XTrust::hasPermission{$expression}) : ?>";
            });

            \Blade::directive('endpermission', function($expression) {
                return "<?php endif; // XTrust::hasPermission ?>";
            });

            \Blade::directive('permissions', function($expression) {
                return "<?php if (\\XTrust::hasPermissions{$expression}) : ?>";
            });

            \Blade::directive('endpermissions', function($expression) {
                return "<?php endif; // XTrust::hasPermissions ?>";
            });


            \Blade::directive('oneofpermissions', function($expression) {
                return "<?php if (\\XTrust::hasOneOfPermissions{$expression}) : ?>";
            });

            \Blade::directive('endoneofpermissions', function($expression) {
                return "<?php endif; // XTrust::hasOneOfPermissions ?>";
            });
            */

    }

}
