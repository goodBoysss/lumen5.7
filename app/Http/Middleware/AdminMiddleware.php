<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\RedisKeyEnum;
use Illuminate\Http\Request;
use App\Exceptions\BasicException;
use App\Services\Tools\JwtService;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        //检查是否有token
        $token = $request->header('token', '');
        if (empty($token)) {
            throw new BasicException(10002);
        }
        //Jwt服务token鉴权
        $admin_id = JwtService::verify_admin_token($token,$request->ip());
        $request->admin_id = $admin_id;
        if (!defined('ADMIN_ID')) {
            define('ADMIN_ID', $admin_id);
        }
        $admin = app('repo_admin')->first(['id' => $admin_id]);
        if (empty($admin)) throw new BasicException(0, '管理员不存在');


        //检测账号是否被其他人登录
        if (env('APP_ENV') == 'production') {
            $redisToken = app('redis')->get(sprintf(RedisKeyEnum::CHANNEL_ADMIN_LOGIN_TOKEN, $admin_id));
            if ($redisToken != $token) {
                throw new BasicException(-400, '账号在其它地方登录，您已掉线');
            }
        }

        return $next($request);
    }
}
