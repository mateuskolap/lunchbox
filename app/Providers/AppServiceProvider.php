<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Http;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $di = [
            WhatsAppProviderInterface::class => EvolutionProvider::class,
        ];

        foreach ($di as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Http::macro('evolution', function () {
            return Http::withHeader('apikey', config('services.evolution.api_key'))
                ->asJson()
                ->acceptJson()
                ->baseUrl(config('services.evolution.url'));
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn(): ?Password => app()->isProduction()
            ? Password::min(4)
                ->numbers()
            : null,
        );
    }
}
