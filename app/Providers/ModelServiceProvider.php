<?php

namespace App\Providers;

use App\Models\App;
use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    public function register()
    {
        //应用
        $this->app();

    }

    //应用
    private function app()
    {
        //应用
        $this->app->singleton('model_app', App::class);
    }
}
