<?php
namespace app\common\model\user;
use app\api\controller\user\User;
use app\common\model\home\HomeLeaguerInviteModel;
use app\common\model\home\HomeModel;
use filehelper\FileHelper;
use think\Model;
use think\Request;
use think\Db;
class UserModel extends Model
{
    protected $name = "User";
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
            $data['token']=UserModel::settoken();       //token
            $info= UserModel::insertGetId($data);        //注册用户
            UserLogsModel::addLog($info,1,$ip,time(),"");

            //如果没有家庭有创建一个家庭
            HomeModel::createHome($info, "默认家庭");
            if ($info!=false){
                $arr['token']=$data['token'];
                $arr['username']= $data['username'];
                return $arr;
            }else{
                return 0;
            }
        }else{
            $data['last_login_ip']=$ip;                 //最后一次登录IP
            $data['last_login_time']=time();           //最后一次登录时间
            $data['token']=UserModel::settoken();                  //token

            $info= UserModel::where("mobile='".$mobile."'")->update($data);  //用户登录
            $username=UserModel::where("mobile='".$mobile."'")->find();  //用户登录

            if ($username['status']==4){
                echo api_return_json(133,'该账号已被禁用');
            }

            UserLogsModel::addLog($username['user_id'],1,$ip,time(),"");
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
    public function updateMobile($mobile,$token)
    {
        $info=UserModel::where("mobile='".$mobile."'")->find();
        if (!empty($info)){
            echo api_return_json(120,'要修改的手机号码已存在');
        }

        $data['mobile']=$mobile;
        return UserModel::where("token='".$token."'")->update($data);

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
        if(empty($token)){
            return null;
        }
        return UserModel::where("token='".$token."'")->field("user_id,username,mobile,ifnull(email,'') as email,avatar,token")->find();
    }


    //退出登录
    public function logout($token)
    {
        $data['token']='';
        return UserModel::where("token='".$token."'")->update($data);
    }

    /*
     * 获取token
     */
    public static function getToken()
    {
        $info = Request::instance()->header();  //获取请求头信息
        $_token = isset($info['token']) ? $info['token'] : "";
        $_token = empty($_token) ? input("post.token") : $_token;
        $_token = empty($_token) ? input("get.token") : $_token;
        $_token = empty($_token) ? "" : $_token;

        return $_token;
    }

    /**
     * @return string
     * 生成token
     */
    public static function settoken()
    {
        $str = md5(uniqid(md5(microtime(true)),true));  //生成一个不会重复的字符串
        $str = sha1($str);  //加密
        return $str;
    }

    //后台修改用户
    public function htUpdateUInfo($input)
    {
        $info=UserModel::where("mobile='".$input['mobile']."'")->find();
        //该手机号已存在
        if ($input['user_id']!=$info['user_id'] && !empty($info)){
            return 1;
        }

        $info=UserModel::where('user_id='.$input['user_id'])->Update($input);
        if ($info==false){
            return 0;
        }else{
            return 2;
        }
    }
    /**
     * 根据管理员ID删除用户头像
     * @param int $id 管理员ID
     */
    public static function rmAvatarByid($id){
        $vo = UserModel::field("avatar")->find($id);
        if($vo && $vo['avatar']){
            FileHelper::helper()->unlink($vo['avatar']);
        }
    }
    //后台添加用户
    public function addHtUserInfo($input)
    {
        $request = Request::instance();
        $ip=$request->ip();

        $info=UserModel::where("mobile='".$input['mobile']."'")->find();
        //该手机号已存在
        if (!empty($info)){
            return 1;
        }

        $input['last_login_ip']=$ip;            //最后一次登录IP
        $input['last_login_time']=time();       //最后一次登录时间
        $input['reg_ip']=$ip;                     //注册IP
        $input['reg_time']=time();                   //注册时间
        $input['reg_type']=1;                        //注册类型

        $info=UserModel::insert($input);
        if ($info==false){
            return 0;
        }else{
            return 2;
        }
    }


    //根据token获取用户ID
   public static function getTokenId()
    {
        $token=UserModel::getToken();
        if ($token==''){
            echo api_return_json(106, "token不能为空");
        }

        $user_id=UserModel::where("token='".$token."'")->value('user_id');
        if ($user_id!==null){
            return $user_id;
        }else{
            return 0;
        }
    }

    /**
     * 根据用户的唯一属性获取用户ID
     * @param $attr
     * @param $value
     */
    public static function getUserIdByAttr($attr, $value){
        $user_id = UserModel::where($attr, $value)->value('user_id');
        return (int)$user_id;
    }

    /**
     * 根据用户ID获取用户指定属性
     * @param $attr
     * @param $user_id
     */
    public static function getAttrByUserId($attr, $user_id){
        $attr = UserModel::where("user_id", $user_id)->value($attr);
        return $attr;
    }

    //保存第三方信息
    public static function saveInfo($openid,$mobile)
    {
        Db::startTrans();
        $info=UserModel::where("mobile='".$mobile."'")->find();
        try{
            //判断手机号是否存在
            if (!empty($info)) {
                UserModel::yesbinding($openid, $info['user_id']);
                Db::commit();
                $data['token']=$info['token'];
                $data['username']=$info['username'];
                echo api_return_json(0,$data);
            }else{
                $user_id=UserModel::nobinding($openid, $info['user_id'],$mobile);
                Db::commit();
                $info=UserModel::where('user_id='.$user_id)->field('username,token')->find();
                echo api_return_json(0,$info);
            }
        }catch (\Exception $e){
            Db::rollback();
            api_return_json(1, $e->getMessage());
        }
    }

    //手机号以前绑定过
    public static function yesbinding($openid,$user_id)
    {
        //获取新微信信息
        $weixin_data=UserWeixinModel::where("third_id='".$openid."'")->find();
        $weixin_data=json_decode($weixin_data['third_data'],true);
        $avatar=getImage($weixin_data['headimgurl'],'uploads/weixin/'.date('Ymd'),time().".jpg",1);
        //如果存在并且没有绑定过微信那就关联起来
        $info=Db::name('user_weixin')->where("user_id=".$user_id)->find();
        if (empty($info)){
            $wx_data['wx_openid']=$openid;
            $wx_data['user_id']=$user_id;
            Db::name('user_weixin')->create($wx_data);

            $u_data['avatar']=$avatar['save_path'];
            $u_data['username']=json_encode($weixin_data['nickname']);
            UserModel::where("user_id='".$user_id."'")->update($u_data);
        }else{
            //如果绑定过微信就覆盖以前的微信信息
            $data['wx_openid']=$openid;
            Db::name('user_weixin')->where("user_id=".$info['user_id'])->update($data);
            //修改用户信息
            $userData['avatar']=$avatar['save_path'];
            $userData['username']=json_encode($weixin_data['nickname']);
            UserModel::where("user_id='".$user_id."'")->update($userData);
        }
        //然后删除缓存的QQ或微信信息
        UserWeixinModel::where("third_id='".$openid."'")->detele();
        UserModel::rmAvatarByid($user_id);//删除原图片
    }

    //手机号以前没有绑定过
    public static function nobinding($openid,$user_id,$mobile)
    {

        $request = Request::instance();
        $ip=$request->ip();
        //获取新微信信息
        $weixin_data=UserWeixinModel::where("third_id='".$openid."'")->find();
        $weixin_data=json_decode($weixin_data['third_data'],true);
        $avatar=getImage($weixin_data['headimgurl'],'uploads/weixin/'.date('Ymd'),time().".jpg",1);


        $data['username']=json_encode($weixin_data['nickname']).rand(1,5);
        $data['mobile']=$mobile;
        $data['avatar']=$avatar['save_path'];;
        $data['last_login_ip']=$ip;
        $data['last_login_time']=time();
        $data['reg_ip']=$ip;
        $data['reg_time']=time();
        $data['token']=UserModel::settoken();

        $user_id=UserModel::insertGetId($data);
        $wx_data['user_id']=$user_id;
        $wx_data['wx_openid']=$openid;
        Db::name('user_weixin')->insert($wx_data);
        //然后删除缓存的QQ或微信信息
        UserWeixinModel::where("third_id='".$openid."'")->delete();
        HomeModel::createHome($user_id, "默认家庭");
        return $user_id;
    }

}
