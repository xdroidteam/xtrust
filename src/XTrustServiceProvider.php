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
        $app = $this->app;
        $version = $app::VERSION;
        if(starts_with($this->app::VERSION, '5.5')){
            \Blade::if('permission', function($expression) {
                return XTrust::hasPermission($expression);
            });

            \Blade::if('permissions', function($expression) {
                return XTrust::hasPermissions($expression);
            });

            \Blade::if('oneofpermissions', function($expression) {
                return XTrust::hasOneOfPermissions($expression);
            });

        } else {
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
        }

    }

}
