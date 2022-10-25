<?php
/**
 * 框架异常处理类(用于错误状态抛出,阻断程序进行)
 * panglishan
 * 2020.07.23
 */
namespace App\Exceptions;

use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class BasicException extends \Exception implements HttpExceptionInterface {

    protected $message;

    public function __construct($code,$msg=null)
    {

        if(!$msg) {
            $this->message = $this->getInfo($code);
        } else {
            $this->message = $msg;
        }

        parent::__construct($this->message, $code);

    }

    public function getInfo($code)
    {
        $message = isset(Config::get("codeinfo")[$code]) ? Config::get("codeinfo")[$code] : '错误未知';

        return $message;
    }

    //解决抛出错误信息被dingo劫持返回500status_code的问题
    public function getStatusCode()
    {
        return 200;
    }

    public function getHeaders()
    {
        return [];
    }
}