<?php

namespace App\Providers;

use App\Repositories\AppRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        // 应用
        $this->app();

    }

    // 应用
    private function app()
    {
        // 应用
        $this->app->singleton('repo_app', AppRepository::class);
    }
}
