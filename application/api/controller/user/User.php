<?php
namespace app\api\controller\user;
use app\common\model\user\UserModel;
use app\api\controller\Father;
class User extends  Father
{
    /**
     *登录&注册
     */
    public function login()
    {

        $user=new UserModel();
        $mobile   =   input('mobile');  //登录名
        $v_code     =   input('v_code');    //验证码
        if ($mobile==''){
            echo api_return_json(1,'手机号不能为空');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }

//        if ($v_code=='')
//        {
//            echo api_return_json(1,'验证码不能为空');
//        }
        $info=$user->Login($mobile);
        if ($info!==0){
            echo api_return_json(0,array('token'=>$info));
        }else{
            echo api_return_json(1,'登录失败');
        }
    }

    /**
     * 修改手机号码
     */
    public function updateMobile()
    {
        $user=new UserModel();

        $used_mobile     =   input('used_mobile');    //旧手机号
        $new_mobile     =   input('new_mobile');     //新手机号
        $v_code     =   input('v_code');    //验证码
        if ($used_mobile=='' || $new_mobile==''){
            echo api_return_json(1,'手机号不能为空');
        }
        if ($used_mobile == $new_mobile){
            echo api_return_json(1,'两个号码不能相同');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$used_mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$new_mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }

//        if ($v_code==''){
//            echo api_return_json(1,'验证码不能为空');
//        }

        $info=$user->updateMobile($used_mobile,$new_mobile);
        if ($info===false) {
            echo api_return_json(1,"修改失败");
        }else{
            echo api_return_json(0);
        }
    }

    //编辑用户姓名
    function exitUserName()
    {
        $user=new UserModel();
        $token=getToken();
        $username=input('username');
        if (empty($token)){
            echo api_return_json(1,"token不能为空");
        }
        if ($username==''){
            echo api_return_json(1,"用户名不能为空");
        }

        $info=$user->exitUserName($token,$username);
        if($info===false){
            echo api_return_json(1,"修改失败");
        }else{
            echo api_return_json(0);
        }
    }


}
