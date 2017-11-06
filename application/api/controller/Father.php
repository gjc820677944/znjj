<?php
namespace app\api\controller;
use app\common\model\user\UserModel;
use think\Db;
use think\Request;
class Father
{
    function __construct()
    {

        $info = Request::instance()->header();  //获取请求头信息
        $url=$_SERVER['REQUEST_URI'];
        $array=array('getWeixinInfo','login','getv_code','bindingPhone');//$url[count($url)-1]
        for($i=0;$i<count($array);$i++){
            if (strpos($url,$array[$i])){
                return;
            }
        }
            //获取token
            $_token = isset($info['token']) ? $info['token'] : "";
            $_token = empty($_token) ? input("post.token") : $_token;
            $_token = empty($_token) ? input("get.token") : $_token;
            $_token = empty($_token) ? "" : $_token;

            if ($_token==''){
                echo api_return_json(101,'token不能为空');
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