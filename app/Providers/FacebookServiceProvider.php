<?php

namespace App\Providers;

use Facebook\Facebook;
use Illuminate\Support\ServiceProvider;

class FacebookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->singleton(UrlDetectionInterface::class, fn($app) => $app[UrlDetectionHandler::class]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
