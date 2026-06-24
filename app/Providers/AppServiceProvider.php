<?php

namespace App\Providers;

use App\Services\WhatsAppServiceInterface;
use App\Services\EvolutionWhatsAppServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Http;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $di = [
            WhatsAppServiceInterface::class => EvolutionWhatsAppServiceInterface::class,
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
                ->baseUrl(config('services.evolution.url') . ':' . config('services.evolution.port'));
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
