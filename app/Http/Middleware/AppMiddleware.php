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
 * @date: 2023/02/17
 * @version: v1.0.0
 * @since: 2023/02/17 09:11
 */


namespace App\Http\Middleware;

use App\Enums\ContextEnum;
use App\Services\Tools\AppService;
use Closure;
use App\Exceptions\BasicException;
use Illuminate\Http\Request;

class AppMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $outAppId = $request->header('appid', '');
        if (empty($outAppId)) {
            throw new BasicException(10001, '应用唯一标识不能为空');
        }

        if ($outAppId == env("GENERAL_APP_ID")) {
            app("context")->set(ContextEnum::APP_ID, 0);
            $appSecret = env("GENERAL_APP_SECRET");
        } else {
            //获取应用信息
            $appInfo = app("logic_cache_app")->getAppByOutAppId($outAppId, array('id', 'app_secret'));
            if (empty($appInfo)) {
                throw new BasicException(10001, '应用不存在');
            }

            app("context")->set(ContextEnum::APP_ID, $appInfo['id']);
            $appSecret = $appInfo['app_secret'];
        }

        if (env("APP_ENV") != "production") {
            return $next($request);
        }
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
        $params['appid'] = $outAppId;

        //签名准确性校验
        $signGenerate = AppService::generateSign($params, $appSecret);

        if ($sign != $signGenerate) {
            throw new BasicException(10001, '签名不合法');
        }

        return $next($request);

    }

}
