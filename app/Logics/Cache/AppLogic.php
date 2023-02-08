<?php
/**
 * AppLogic.php
 * ==============================================
 * Copy right 2015-2021  by https://www.tianmtech.com/
 * ----------------------------------------------
 * This is not a free software, without any authorization is not allowed to use and spread.
 * ==============================================
 * @desc : 应用缓存
 * @author: zhanglinxiao<zhanglinxiao@tianmtech.cn>
 * @date: 2023/01/11
 * @version: v1.0.0
 * @since: 2023/01/11 09:11
 */

namespace App\Logics\Cache;

use App\Enums\RedisKeyEnum;

class AppLogic
{

    /**
     * @desc: 获取应用列表
     * @param array $params
     *      id    应用唯一ID，对应ln_app字段id
     *      app_id   应用外部ID，对应ln_app字段app_id
     *      alias   应用公共服务统一别名
     * @param array $fields
     * @return array
     * @author zhanglinxiao <zhanglinxiao@tianmtech.cn>
     * @datetime 2022/07/21
     */
    public function getAppList($params = array(), $fields = array("*"))
    {
        $appList = array();
        //过期时间：秒
        $expireSeconds = 30;
        $redisKey = RedisKeyEnum::APP;
        $redisValue = app("redis")->get($redisKey);
        if (!empty($redisValue)) {
            $appList = json_decode($redisValue, true);
        } else {
            $where = array();

            $appList = app('repo_app')->get($where, array("*"))->keyby('id')->toArray();

            app("redis")->set($redisKey, json_encode($appList, JSON_UNESCAPED_UNICODE));
            app("redis")->expire($redisKey, $expireSeconds);
        }

        //过滤字段
        $filterKeys = array('id', 'app_id', 'alias');

        //过滤参数非指定过滤字段，直接返回空数组
        foreach ($params as $k => $v) {
            if (!in_array($k, $filterKeys)) {
                return array();
            }
        }

        //删选过滤条件
        $appList = array_filter($appList, function ($v) use ($params, $filterKeys) {
            foreach ($filterKeys as $key) {
                if (isset($params[$key]) && isset($v[$key]) && $params[$key] != $v[$key]) {
                    return false;
                }
            }
            return true;
        });

        //输出字段过滤
        if (!empty($fields) && is_array($fields) && $fields != array('*')) {
            foreach ($appList as $k => $appInfo) {
                $newInfo = array();
                foreach ($fields as $field) {
                    if (isset($appInfo[$field])) {
                        $newInfo[$field] = $appInfo[$field];
                    } else {
                        return array();
//                        $newInfo[$field] = "";
                    }
                }
                $appList[$k] = $newInfo;
            }
        }
        return array_values($appList);
    }

    /**
     * @desc: 通过id获取应用信息
     * @param $id
     * @param array $fields
     * @return array
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * DateTime: 2023/01/11 18:18
     */
    public function getAppById($id, $fields = array("*"))
    {
        $appList = $this->getAppList(array(
            'id' => $id
        ), $fields);
        if (!empty($appList[0])) {
            $appInfo = $appList[0];
        } else {
            $appInfo = array();
        }
        return $appInfo;
    }

    /**
     * @desc: 通过外部appid（app表中的字段app_id）获取应用信息
     * @param $outAppId
     * @param array $fields
     * @return array
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * DateTime: 2023/01/11 18:18
     */
    public function getAppByOutAppId($outAppId, $fields = array("*"))
    {
        $appList = $this->getAppList(array(
            'app_id' => $outAppId
        ), $fields);
        if (!empty($appList[0])) {
            $appInfo = $appList[0];
        } else {
            $appInfo = array();
        }
        return $appInfo;
    }

    /**
     * @desc: 通过alias获取应用信息
     * @param string $alias
     * @param array $fields
     * @return array
     * User: zhanglinxiao<zhanglinxiao@tianmtech.cn>
     * DateTime: 2023/01/11 18:18
     */
    public function getAppByAlias($alias, $fields = array("*"))
    {
        $appList = $this->getAppList(array(
            'alias' => $alias
        ), $fields);
        if (!empty($appList[0])) {
            $appInfo = $appList[0];
        } else {
            $appInfo = array();
        }
        return $appInfo;
    }

}
