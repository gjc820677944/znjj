<?php
namespace app\api\controller\user;
use app\common\model\admin\UserModel;
use app\api\controller\Father;
class User extends  Father
{
    /**
     *
     */
    public function login()
    {

        $user=new UserModel();
        $username   =   input('username');  //登录名
        $v_code     =   input('v_code');    //验证码
        if ($username==''){
            echo api_return_json(1,'手机号不能为空');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$username)){
            echo api_return_json(1,'请输入正确手机号');
        }

        $info=$user->Login($username);
        if ($info!==0){
            echo api_return_json(0,array('token'=>$info));
        }else{
            echo api_return_json(1,'登录失败');
        }
    }
}
