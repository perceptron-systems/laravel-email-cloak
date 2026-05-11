<?php

declare(strict_types=1);

namespace PerceptronSystems\EmailCloak;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class EmailCloakServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/email-cloak.php', 'email-cloak');

        $this->app->singleton(EmailCloak::class);
    }

    public function boot(): void
    {
        $this->registerRateLimiter();
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'email-cloak');
        $this->registerBladeDirective();
        $this->registerPublishables();
    }

    private function registerRateLimiter(): void
    {
        RateLimiter::for('email-cloak', function (Request $request) {
            return Limit::perMinute(
                (int) config('email-cloak.rate_limit', 30)
            )->by($request->ip());
        });
    }

    private function registerBladeDirective(): void
    {
        Blade::directive('cloakedEmail', function (string $expression): string {
            return "<?php echo app(\\PerceptronSystems\\EmailCloak\\EmailCloak::class)->render({$expression}); ?>";
        });
    }

    private function registerPublishables(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/email-cloak.php' => config_path('email-cloak.php'),
        ], 'email-cloak-config');

        $this->publishes([
            __DIR__.'/../resources/css/email-cloak.css' => public_path('vendor/email-cloak/email-cloak.css'),
        ], 'email-cloak-assets');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/email-cloak'),
        ], 'email-cloak-views');
    }
}
