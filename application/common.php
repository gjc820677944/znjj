<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 定义接口返回数据格式
 * @param int $code 错误码 $code
 * @param null $data 返回数据或错误提示信息
 */
function api_return_json($code = 0, $data = null)
{
    header("Access-Control-Allow-Origin: http://localhost:8080");
    header("Access-Control-Allow-Credentials:true");
    if($code === 0){
        $msg = '请求成功';
    }
    else{
        $msg = $data;
        $data = null;
    }
    $result = [
        'code' => $code,
        'data' => $data,
        'msg' => $msg,
    ];
    exit(json_encode($result));
}