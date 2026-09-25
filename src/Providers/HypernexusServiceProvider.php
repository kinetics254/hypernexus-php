<?php

namespace KTL\Hypernexus\Providers;

use Illuminate\Support\ServiceProvider;
use KTL\Hypernexus\BusinessCentral;
use KTL\Hypernexus\Endpoint\EndpointRegistry;
use KTL\Hypernexus\Http\BusinessCentralClient;

class HypernexusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/hypernexus.php',
            'hypernexus'
        );

        $this->app->singleton(
            BusinessCentralClient::class,
            fn () => new BusinessCentralClient(
                config('hypernexus.company')
            )
        );

        $this->app->singleton(
            EndpointRegistry::class
        );

        $this->app->singleton(
            BusinessCentral::class,
            fn ($app) => new BusinessCentral(
                $app->make(BusinessCentralClient::class),
                $app->make(EndpointRegistry::class),
            )
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../Config/hypernexus.php' =>
                config_path('hypernexus.php'),
        ], 'hypernexus-config');
    }
}