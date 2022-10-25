<?php


namespace App\Request;


use App\Exceptions\BasicException;

class Validator
{
    //公共字段
    const PUBLIC_RULES = [
        'page' => 'integer|min:1',
        'page_size' => 'integer|min:1|max:100',
    ];

    //参数验证
    public static function paramVerify($fields, $params, $request, $required = true)
    {
        $request = new $request;

        //获取控制器配置的所有规则及说明
        $allRules = $request->rules($required);
        $allMessages = $request->messages();

        //提取目标字段对应的验证规则
        $rules = self::getValidateRule($allRules, $fields);
        $rules += self::PUBLIC_RULES;

        //验证
        $v = app('validator')->make($params, $rules, $allMessages);
        if ($v->fails()) throw new BasicException(10001, $v->errors()->first());
    }

    //获取验证规则
    private static function getValidateRule($allRules, $fields)
    {
        $rule = [];
        foreach ($fields as $field) {
            if (isset($allRules[$field])) {
                $rule[$field] = $allRules[$field];
            }
        }
        return $rule;
    }
}
