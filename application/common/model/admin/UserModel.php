<?php

namespace app\common\model\admin;

use think\Request;
use think\Model;
use think\Db;

class UserModel extends Model
{
    protected $name = "User";
//    protected $connection=[
//        'type'		=>		'mysql',		//数据库类型
//        'hostname'	=>		'192.168.31.227',	//服务器地址
//        'database'	=>		'smart_home',			//数据库名
//        'username'	=>		'root',			//数据库名
//        'password'	=>		'1q2w3e4r',			//数据库密码
//        'hostport'	=>		'3306',			//数据库连接端口
//        'params'	=>		'[]',			//数据库连接参数
//        'charset'	=>		'utf8',			//数据库默认编码utf8
//        'prefix'	=>		'smart_',		//数据表前缀
//        'debug'		=>		true,			//数据库调试模式
//    ];

    //用户登录
    public function Login($username){
        $request = Request::instance();
        $ip=$request->ip();

        $info=Db::name('user')->where("username='".$username."'")->find();
        if (empty($info)){
            $data['username']=$username;                //用户名
            $data['mobile']=$username;                  //手机号
            $data['last_login_ip']=$ip;            //最后一次登录IP
            $data['last_login_time']=time();           //最后一次登录时间
            $data['reg_ip']=$ip;                        //注册IP
            $data['reg_time']=time();                   //注册时间
            $data['reg_type']=1;                        //注册类型
            $data['status']=1;                          //登录状态
            $data['token']=settoken();                  //token

           $info= DB::name('user')->insert($data);  //注册用户
            if ($info!==false){
                return $data['token'];
            }else{
                return 0;
            }
        }else{
            $data['last_login_ip']=$ip;            //最后一次登录IP
            $data['last_login_time']=time();           //最后一次登录时间
            $data['token']=settoken();                  //token

            $info= DB::name('user')->where("username='".$username."'")->update($data);  //用户登录
            if ($info!==false){
                return $data['token'];
            }else{
                return 0;
            }
        }

    }

}
