<?php


namespace App\Providers;


use Illuminate\Support\ServiceProvider;

class LogicServiceProvider extends ServiceProvider
{
    //register
    public function register()
    {
        //缓存业务数据
        $this->cache();
    }

    //缓存业务数据
    public function cache()
    {
        //缓存-应用
        $this->app->singleton('logic_cache_app', \App\Logics\Cache\AppLogic::class);

    }

}