<?php
namespace App\Response;

class Response {

    /**
     * 普通响应方法,返回整个数据
     * @param $data
     * @return string
     */
    static function sendData($data = [], $msg = '')
    {
        $array = [
            'code' => 200,
            'data' => $data,
            'message' => $msg,
        ];

        return response()->json($array);
    }


    /**
     * 列表响应方法
     * @param $data 列表主要数据
     * @param array $param  请求参数
     * @param array $ext    扩展数据
     * @param int $code     响应code
     * @return string
     */
    static function sendList($data,$param=[],$ext=[],$code=200)
    {
        $list = $data->items();        //取得列表中的data项
        $page = $data->currentPage();
        $limit = $data->perPage();
        $next = $data->nextPageUrl();
        $total = $data->total();
        if(!empty($next) && !empty($param)) $next .= '&'.http_build_query($param);

        if(empty($ext)) {
            $array = [
                'code' => $code,
                'data' => [
                    'list' => $list,
                    'meta' => [
                        'page' => $page,
                        'limit' => $limit,
                        'next' => $next,
                        'total' => $total
                    ]
                ]
            ];
        } else {
            $array = [
                'code' => $code,
                'data' => [
                    'list' => $list,
                    'meta' => [
                        'page' => $page,
                        'limit' => $limit,
                        'next' => $next,
                        'total' => $total
                    ],
                    'header' => $ext
                ]
            ];
        }
        return response()->json($array);
    }

}
