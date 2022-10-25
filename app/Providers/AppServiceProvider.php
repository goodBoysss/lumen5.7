<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;

//引用http请求类

class AppServiceProvider extends ServiceProvider
{

    //boot
    public function boot()
    {
//        // 将所有的 Exception 全部交给 App\Exceptions\Handler 来处理
//        app('api.exception')->register(function (\Exception $exception) {
//            $request = Request::capture();
//            return app('App\Exceptions\Handler')->render($request, $exception);
//        });

    }

    public function register()
    {
        if ($this->app->environment() !== 'production') {
            $this->app->register(IdeHelperServiceProvider::class);
        }
    }
}
