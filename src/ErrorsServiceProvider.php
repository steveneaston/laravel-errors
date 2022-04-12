<?php

namespace Seaston\LaravelErrors;

use Illuminate\Support\ServiceProvider;
use Seaston\LaravelErrors\ViewErrorBag;
use Illuminate\Contracts\Support\DeferrableProvider;

class ErrorsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/errors.php' => config_path('errors.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/errors.php', 'errors');

        $this->app->singleton(ViewErrorBag::class, function ($app) {
            $errorBag = new ViewErrorBag;
            $errorBag->setClasses($app['config']->get('errors.classes', []));
            $errorBag->setMessages($app['config']->get('errors.messages', []));
            return $errorBag;
        });
    }

    public function provides()
    {
        return [ViewErrorBag::class];
    }
}
