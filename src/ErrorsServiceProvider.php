<?php

namespace Seaston\LaravelErrors;

use Illuminate\Support\ServiceProvider;
use Seaston\LaravelErrors\ViewErrorBag;

class ErrorsServiceProvider extends ServiceProvider
{
    protected $defer = true;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('form-errors', function ($app) {
            return new ViewErrorBag;
        });
    }

    public function provides()
    {
        return ['form-errors'];
    }
}
