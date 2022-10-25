<?php
/**
 * AppMiddleware.php
 * ==============================================
 * Copy right 2015-2021  by https://www.tianmtech.com/
 * ----------------------------------------------
 * This is not a free software, without any authorization is not allowed to use and spread.
 * ==============================================
 * @desc : 超级管理员操作日志
 * @author: zhanglinxiao<zhanglinxiao@tianmtech.cn>
 * @date: 2021/04/25
 * @version: v1.0.0
 * @since: 2021/04/25 09:11
 */

namespace App\Http\Middleware;

use Closure;


class AdminLogMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $adminId = defined("ADMIN_ID") ? ADMIN_ID : 0;
        $appId = (int)$request->input("app_id",0);
        $ip = $request->ip();
        $uri = $request->decodedPath();
        $method = strtolower($request->method());
        //由于上传的文件也在$request->all()中，用$_REQUEST替换
//        $params = $request->all();
        $params = $_REQUEST;
        app('repo_admin_action_log')->insert([
            'admin_id' => $adminId,
            'app_id' => $appId,
            'ip' => $ip,
            'uri' => $uri,
            'method' => $method,
            'params' => json_encode($params, JSON_UNESCAPED_UNICODE),
        ]);
        return $next($request);
    }
}
