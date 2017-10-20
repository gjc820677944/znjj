<?php
namespace app\admin\controller\user;
use app\common\model\admin\UserModel;

class User
{
    public function register()
    {
        $user=new UserModel();

        if (input('username')==""){
           echo api_return_json(146,"用户名不能为空");
        }

        if (input('password')==""){
            echo api_return_json(146,"密码不能为空");
        }

        $data['$username']          =   input('username');          //用户名
        $data['password']           =   md5(input('password'));     //用户密码
        $data['mobile']             =   input('mobile');             //手机号码
        $data['email']              =   input('email');              //邮箱
        $data['last_login_ip']     =   $_SERVER["REMOTE_ADDR"];         //最后一次登录IP
        $data['last_login_time']   =   time();                            //最后一次登录时间
        $data['reg_type']           =   0;                                 //注册端
        $data['status']             =   1;                                  //登录状态 1正常 4禁用

//        $info=$user->insert($data);
        var_dump($data);
//        $info=$user->register($data);
        if ($info){
            echo api_return_json(0);
        }else{
           echo api_return_json(105,"参数错误");
        }
    }

}
