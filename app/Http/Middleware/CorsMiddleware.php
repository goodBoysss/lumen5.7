<?php
/**
 * AppMiddleware.php
 * ==============================================
 * Copy right 2015-2021  by https://www.tianmtech.com/
 * ----------------------------------------------
 * This is not a free software, without any authorization is not allowed to use and spread.
 * ==============================================
 * @desc : 处理web端跨域的中间件
 * @author: zhanglinxiao<zhanglinxiao@tianmtech.cn>
 * @date: 2023/02/17
 * @version: v1.0.0
 * @since: 2023/02/17 09:11
 */


namespace App\Http\Middleware;

use Closure;


class CorsMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->getMethod() == "OPTIONS") {
            return response()->json('ok', 200, [
                # 下面参数视request中header而定
                'Access-Control-Allow-Origin'  => '*',
                'Access-Control-Allow-Headers' => 'Origin, Content-Type, Cookie, X-Requested-With, Accept, token ,sign, timestamp',
                //'Access-Control-Allow-Headers' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, OPTIONS, DELETE',
            ]);
        }

        $response = $next($request);

        //兼容下载返回的$response 无header方法
        if (method_exists($response,'header')){
            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, X-Requested-With, Accept, multipart/form-data, application/json, token, text/plain, */*, sign, timestamp');
            //$response->header('Access-Control-Allow-Headers', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS, DELETE');
            $response->header('Access-Control-Allow-Credentials', 'false');
        }



        return $response;
    }
}
