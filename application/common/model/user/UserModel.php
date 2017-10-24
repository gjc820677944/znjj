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
           $info= UserModel::insertGetId($data);  //注册用户
            addLog($info,1,$ip,time(),"");
            if ($info!=false){
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
            addLog($username['user_id'],1,$ip,time(),"");
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

    //保存微信信息
    function saveWeixinInfo($avatar,$wx_openid,$wx_unionid,$nickname)
    {

        $request = Request::instance();
        $ip=$request->ip();

        $where['wx_openid']=$wx_openid;
        $where['wx_unionid']=$wx_unionid;
        $info=Db::name('user_weixin')->where($where)->find();
        if (empty($info)){
            $u_data['username']=json_encode($nickname);                //用户名
            $u_data['last_login_ip']=$ip;            //最后一次登录IP
            $u_data['last_login_time']=time();       //最后一次登录时间
            $u_data['reg_ip']=$ip;                     //注册IP
            $u_data['reg_time']=time();                   //注册时间
            $u_data['reg_type']=1;                        //注册类型
            $u_data['status']=1;                          //登录状态
            $u_data['avatar']=$avatar['save_path'];       //头像
            $u_data['token']=settoken();                  //token


            $data['wx_openid']=$wx_openid;
            $data['wx_unionid']=$wx_unionid;

            Db::startTrans();
            try{
               $user_id= UserModel::insertGetId($u_data);    //添加用户
                addLog($user_id,1,$ip,time(),"");
                $data['user_id']=$user_id;
                Db::name('user_weixin')->insert($data);
                Db::commit();
                return Db::name('user_weixin')->alias('uw')->where($where)
                            ->field("u.user_id,u.username,ifnull(u.mobile,'') as mobile,u.avatar,u.token")
                            ->join('user u','u.user_id=uw.user_id','left')
                            ->find();
            }catch (\Exception $e){
                Db::rollback();
                return 0;
            }

        }else{
            
            $data= Db::name('user_weixin')->alias('uw')->where($where)
                ->field("u.user_id,u.username,ifnull(u.mobile,'') as mobile,u.avatar,u.token")
                ->join('user u','u.user_id=uw.user_id','left')
                ->find();
            addLog($data['user_id'],1,$ip,time(),"");
            return $data;
        }
    }

}
