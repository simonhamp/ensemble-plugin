<?php

namespace SimonHamp\Ensemble;

use Illuminate\Support\ServiceProvider;

class EnsembleServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (! env('ENSEMBLE_ENABLED', false)) {
            return;
        }

        $this->registerRoutes();
    }

    protected function registerRoutes()
    {
        $this->app['router']->post(
            env('ENSEMBLE_ENDPOINT', 'ensemble'),
            '\\SimonHamp\\Ensemble\\Controller@ensemble'
        );
    }
}
