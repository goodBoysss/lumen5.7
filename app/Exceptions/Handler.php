<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\BasicException;  //引入框架异常处理类
use Illuminate\Support\Facades\Config;  //引入配置类

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * 重构日志记录方法,直接发送至sentry
     */
    public function report(Exception $exception)
    {
        if($exception instanceof BasicException)
        {
            //框架自定义异常消息--上报sentry
            /*$exceptionMessage = $exception->getCode().' '.$exception->getMessage();
            if(env('BASE_ISSENTRY')) {
                //todo:待进一步处理,方便在sentry后台快速发现日志异常问题
                app('sentry')->captureException(new Exception($exceptionMessage));
            }*/

        } else {
            if(env('BASE_ISSENTRY')) {
                //todo
                app('sentry')->captureException($exception);
            } else {
                parent::report($exception);
            }
        }
    }


    /**
     * 重构异常上报方法
     */
    public function render($request, Exception $exception)
    {
        if($exception instanceof BasicException)
        {
            return response()->json(['code'=>$exception->getCode(),'message'=>$exception->getMessage()]);
        } else {
            if(env('BASE_RENDEREXCEPTION')) {
                return parent::render($request, $exception);
            } else {
                return response()->json(['code'=>520,'message'=>$exception->getMessage()]);
            }
        }
    }
}
