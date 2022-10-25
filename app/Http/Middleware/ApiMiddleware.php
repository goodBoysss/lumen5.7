<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\RedisKeyEnum;
use Illuminate\Http\Request;
use App\Exceptions\BasicException;
use App\Services\Tools\JwtService;

class ApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        //检查是否有token
        $token = $request->header('token', '');
        if (empty($token)) {
            throw new BasicException(10002);
        }
        //Jwt服务token鉴权
        $admin_id = JwtService::verify_token($token);
        $request->admin_id = $admin_id;

        $admin = app('repo_admin')->first(['id' => $admin_id], ['role_id']);
        if (empty($admin)) throw new BasicException(0, '管理员不存在');
        $request->role_id = $admin->role_id;

        //检测token是否被其它管理员登录顶替
//        if (env('APP_ENV') == 'production') {
//            $redisToken = app('redis')->get(sprintf(RedisKeyEnum::ADMIN_LOGIN_TOKEN, $admin_id));
//            if ($redisToken != $token) {
//                throw new BasicException(-400, '账号在其它地方登录，您已掉线');
//            }
//        }

        //获取管理员菜单的接口不验证权限
        $ignorePaths = array("access/menu/admin-menu", "config/app/options", "access/admin/switch-app");
        if (in_array($request->decodedPath(), $ignorePaths)) {
            return $next($request);
        }
        //权限验证，role_id=0表示超级管理员，>0为其他管理员
        if ($admin->role_id > 0) {
            $method = strtolower($request->method());
            $roleRoutes = app('repo_access')->getRoleAccessRoutes($admin->role_id);
            foreach ($roleRoutes as $route) {
                if ($route['method'] == $method && $request->is($route['uri'])) {
                    return $next($request);
                }
            }
            throw new BasicException(10005);
        }

        return $next($request);
    }
}
