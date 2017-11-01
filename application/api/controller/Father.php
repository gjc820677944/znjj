<?php
namespace app\api\controller;
use app\common\model\user\UserModel;
use think\Db;
class Father
{
    function __construct()
    {
        $url=$_SERVER['REQUEST_URI'];
        $url=explode('/',$url);
        if ($url[count($url)-1]!='login'){
            //获取token
            $_token = isset($_SERVER["HTTP_TOKENA"]) ? $_SERVER["HTTP_TOKENA"] : "";
            $_token = empty($_token) ? input("post.token") : $_token;
            $_token = empty($_token) ? input("get.token") : $_token;
            $_token = empty($_token) ? "" : $_token;

            if ($_token==''){
                echo api_return_json(106,'token不能为空');
            }

            if(!empty($_token)){
                $_row = DB::name("user")->where(array("status"=>1,"token"=>$_token))->find();
                if (count($_row)==0) {
                    echo api_return_json(106,'token不存在');
                    exit;
                }
            }
        }
    }
}