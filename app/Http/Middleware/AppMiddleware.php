<?php
/**
 * AppMiddleware.php
 * ==============================================
 * Copy right 2015-2021  by https://www.tianmtech.com/
 * ----------------------------------------------
 * This is not a free software, without any authorization is not allowed to use and spread.
 * ==============================================
 * @desc : 应用中间件
 * @author: zhanglinxiao<zhanglinxiao@tianmtech.cn>
 * @date: 2021/11/03
 * @version: v1.0.0
 * @since: 2021/11/03 09:11
 */


namespace App\Http\Middleware;

use App\Repositories\AppRepository;
use App\Response\Response;
use App\Services\Tools\AppService;
use Closure;
use Carbon\Carbon;
use App\Exceptions\BasicException;
use Illuminate\Http\Request;

class AppMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        $appId = $request->header('appid', '');
        if (empty($appId)) {
            throw new BasicException(10001, '应用唯一标识不能为空');
        }

        //获取应用信息
        /**@var AppRepository $appRepo */
        $appRepo = app("repo_app");
        $appInfo = $appRepo->first(array(
            'app_id' => $appId
        ), array('id', 'app_secret'));
        if (empty($appInfo)) {
            throw new BasicException(10001, '应用不存在');
        }
        define("APP_ID", $appInfo['id']);
        //TODO::
//        return $next($request);
        //签名
        $sign = $request->header('sign', '');
        if (empty($sign)) {
            throw new BasicException(10001, '签名不能为空');
        }

        //时间检验
        $timestamp = $request->header('timestamp', 0);
        if ((time() - $timestamp) > 300) {
            throw new BasicException(10001, '请求已过期');
        }

        //签名算法--对参数键值对倒序排列,拼接成字符串,加上签名秘钥,md5加密
        $params = $request->all();

        $params['timestamp'] = $timestamp;
        $params['appid'] = $appId;

        //签名准确性校验
        $signGenerate = AppService::generateSign($params, $appInfo->app_secret);

        if ($sign != $signGenerate) {
            throw new BasicException(10001, '签名不合法');
        }

        return $next($request);

    }

}
