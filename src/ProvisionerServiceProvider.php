<?php

namespace Andrewhood125\Provisioner;

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
        $this->commands([
            'Andrewhood125\Provisioner\Commands\ProvisionDebianCommand',
            'Andrewhood125\Provisioner\Commands\InstallCommand'
        ]);
    }
}
