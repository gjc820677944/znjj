<?php
namespace app\api\controller;
use app\common\model\user\UserModel;
use think\Db;
class Father
{
    function __construct()
    {
        //判断管理员是否登录
        $token=UserModel::getToken();
        if(!empty($token)){
                $_row = DB::name("user")->where(array("status"=>1,"token"=>$token))->find();
                if (count($_row)==0) {
                    echo api_return_json(106,'token不存在');
                    exit;
                }
        }
    }
}