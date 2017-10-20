<?php
namespace app\api\controller;
use think\Db;
class Father
{
    function __construct()
    {
        //判断管理员是否登录
        $token=getToken();
        if(!empty($token)){
                $_row = DB::name("user")->where(array("status"=>1,"token"=>$token))->find();
                if (count($_row)==0) {
                    echo api_return_json(1,'请重新登录');
                    exit;
                }
        }
    }
}