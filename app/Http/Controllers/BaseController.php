<?php

namespace App\Http\Controllers;

use App\Exceptions\BasicException;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    use Helpers;


    /**
     * 参数校验
     * @param Request $request
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     * @return array|void
     * @throws BasicException
     */
    public function validate(Request $request, array $rules, array $messages = [], array $customAttributes = []) {

        //参数校验
        $validator = $this->getValidationFactory()->make($request->all(), $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            $msg = "";

            $messages = $validator->messages();
            $error_arr = json_decode($messages, true);
            if (!empty($error_arr) && is_array($error_arr)) {
                //获取一条错误作为错误信息返回
                $first_error_arr=current($error_arr);
                $msg=current($first_error_arr);
            }

            throw new BasicException(10010, $msg);
        }

        return $this->extractInputFromRules($request, $rules);

    }
}
