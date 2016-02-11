<?php

namespace andrewhood125\provisioner;

use Illuminate\Support\ServiceProvider;

class ProvisionerServiceProvider extends ServiceProvider
{
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
        $this->app->singleton('command.andrewhood125.provision', function ($app) {
            return $app['andrewhood125/provisioner/Commands/ProvisionCommand'];
        });
        $this->commands('command.andrewhood125.provision');
    }
}
