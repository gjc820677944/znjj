<?php
namespace app\common\model\user;
use app\api\controller\user\User;
use think\Model;
use think\Request;
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

    //用户登录$注册
    public function Login($mobile){
        $request = Request::instance();
        $ip=$request->ip();
        $info=UserModel::where("mobile='".$mobile."'")->find();
        if (empty($info)){
            $data['username']=$mobile;                //用户名
            $data['mobile']=$mobile;                  //手机号
            $data['last_login_ip']=$ip;            //最后一次登录IP
            $data['last_login_time']=time();           //最后一次登录时间
            $data['reg_ip']=$ip;                        //注册IP
            $data['reg_time']=time();                   //注册时间
            $data['reg_type']=1;                        //注册类型
            $data['status']=1;                          //登录状态
            $data['token']=settoken();                  //token
           $info= UserModel::insert($data);  //注册用户
            if ($info!==false){
                $arr['token']=$data['token'];
                $arr['username']=$info['username']===null?'':$info['username'];
                return $arr;
            }else{
                return 0;
            }
        }else{
            $data['last_login_ip']=$ip;                 //最后一次登录IP
            $data['last_login_time']=time();           //最后一次登录时间
            $data['token']=settoken();                  //token

            $info= UserModel::where("mobile='".$mobile."'")->update($data);  //用户登录
            $username=UserModel::where("mobile='".$mobile."'")->find();  //用户登录
            if ($info!==false){
                $arr['token']=$data['token'];
                $arr['username']=$username['username']==null?'':$username['username'];
                return $arr;
            }else{
                return 0;
            }
        }
    }

    //修改手机号
    public function updateMobile($used_mobile,$new_mobile)
    {
        $info=UserModel::where("mobile='".$used_mobile."'")->find();
        if (empty($info)){
            echo api_return_json(1,'手机号码不存在');
        }

        $info=UserModel::where("mobile='".$new_mobile."'")->find();
        if (!empty($info)){
            echo api_return_json(1,'要修改的手机号码已存在');
        }

        $data['mobile']=$new_mobile;
        $data['username']=$new_mobile;
        return UserModel::where("mobile='".$used_mobile."'")->update($data);

    }

    //编辑用信息
    public function editUserName($token,$username)
    {
        $data['username']=$username;
        return UserModel::where("token='".$token."'")->update($data);
    }


    //获取用户信息
    public function  getUserInfo($token)
    {
        return UserModel::where("token='".$token."'")->field("user_id,username,mobile,ifnull(email,'') as email,avatar,token")->find();
    }


    //退出登录
    public function logout($token)
    {
        $data['token']='';
        return UserModel::where("token='".$token."'")->update($data);
    }

}
