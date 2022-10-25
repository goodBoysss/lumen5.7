<?php
/**
 * 状态码配置文件
 */

return [
    //系统部分校验码(系统提供自动转化验证错误信息的功能,但是校验最好放置前端,后端只做校验通过or不通过)
    "10001" => '输入参数有误',
    "10002" => '缺少用户token',
    "10003" => '非法请求,token参数不合法',
    "10004" => '登录过期,请重新登录',
    "10005" => '您无此权限执行该操作,请联系管理员',
    "10006" => '当前网络环境发生变化,非法请求',
    "10007" => '当前未登录,请重新登录',
    "10008" => '数据有误',


    //服务错误
    "10020" => "curl请求失败,请检查网络连接",


    //文件处理部分
    "10050" => '没有要上传的文件',
    "10051" => '不支持该类型文件上传',
    "10052" => '上传的文件无效,请重新上传',
    "10053" => '上传失败',


    //管理员权限模块(2开头)
    "20001" => '您的登录账号不存在',
    "20002" => '您的账号已被禁用,请联系管理员',
    "20003" => '登录密码错误',
    "20006" => '管理员账号已经存在',
    "20007" => '删除失败',
    "20008" => '管理员编辑失败',
    "20009" => '管理员密码修改失败',
    "20010" => '管理员不存在',
    "20011" => '电话号已经有人使用',

    "20050" => '父级菜单不存在',
    "20051" => '菜单插入失败',
    "20052" => '菜单不存在',
    "20053" => '菜单编辑失败',
    "20054" => '只能添加二级菜单权限',
    "20055" => '权限不存在',
    "20056" => '角色权限值设置错误',
    "20057" => '角色菜单设置错误',
    "20058" => '未获取到菜单缓存数据',
    "20059" => '未获取到权限缓存数据',
    "20060" => '没有该角色',
    "20061" => '没有该缓存标记',
    "20062" => '角色编辑失败',
	
];

?>
