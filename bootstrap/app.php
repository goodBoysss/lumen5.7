<?php

require_once __DIR__.'/../vendor/autoload.php';

try {
    (new Dotenv\Dotenv(dirname(__DIR__)))->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    //
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

//设置默认时区
//date_default_timezone_set('PRC');

//注册配置文件
$app->configure('codeinfo');    //注册错误码文件
$app->configure('filesystem');  //注册文件存储配置
$app->configure('database');    //注册数据库配置文件
$app->configure('view');    //视图路径配置文件
$app->configure('queue');    //队列配置


/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before and after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/

$app->routeMiddleware([
    'api' => App\Http\Middleware\ApiMiddleware::class,
    'cors' => App\Http\Middleware\CorsMiddleware::class,
]);

 $app->middleware([
     App\Http\Middleware\CorsMiddleware::class
 ]);

// $app->routeMiddleware([
//     'auth' => App\Http\Middleware\Authenticate::class,
// ]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/

//注册Dingo服务
//$app->register(Dingo\Api\Provider\LumenServiceProvider::class);
////注册Sentry服务
$app->register(Sentry\Laravel\ServiceProvider::class);
//注册模型服务提供者
$app->register(App\Providers\ModelServiceProvider::class);
//注册逻辑服务提供者
$app->register(App\Providers\LogicServiceProvider::class);
//注册APP服务(用于处理框架内部冲突及全局问题)
$app->register(App\Providers\AppServiceProvider::class);
//注册数据仓库服务
$app->register(App\Providers\RepositoryServiceProvider::class);
//注册redis服务提供者
$app->register(Illuminate\Redis\RedisServiceProvider::class);
//注册事件监听者
$app->register(App\Providers\EventServiceProvider::class);
//注册RabbitMQ服务
$app->register(\VladimirYuldashev\LaravelQueueRabbitMQ\LaravelQueueRabbitMQServiceProvider::class);
//注册laravel-s
$app->register(Hhxsv5\LaravelS\Illuminate\LaravelSServiceProvider::class);

/*
|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

//开启facade,开启eloquent
$app->withFacades();

$app->withEloquent();


$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__.'/../routes/web.php';
});

return $app;
