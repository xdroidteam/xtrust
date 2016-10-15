<?php namespace XdroidTeam\XTrust;

use Illuminate\Support\ServiceProvider;

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
        //
    }

    private function bladeDirectives()
    {
        \Blade::directive('permission', function($expression) {
            return "<?php if (\\XTrust::hasPermission{$expression}) : ?>";
        });

        \Blade::directive('endpermission', function($expression) {
            return "<?php endif; // XTrust::hasPermission ?>";
        });

        \Blade::directive('permissions', function($expression) {
            $perms = explode('|', $expression);
            return "<?php if (\\XTrust::hasPermissions{$perms}) : ?>";
        });

        \Blade::directive('endpermissions', function($expression) {
            return "<?php endif; // XTrust::hasPermissions ?>";
        });

    }

}
