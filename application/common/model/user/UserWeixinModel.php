<?php

namespace app\common\model\user;
use \think\Db;
use think\Model;

class UserWeixinModel extends Model
{
    protected $name = "user_weixin";
    public static function weixinlogin($type,$access_token,$openid)
    {
        $where['wx_openid']=$openid;
        $info=UserWeixinModel::where($where)->find();
        //获取用户微信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN ";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $output = json_decode($output, true);
        if (!isset($output['nickname'])){
            api_return_json(1, "获取微信信息失败");
        }
        $output = json_encode($output);
        curl_close($ch);
        if (empty($info)){
            $data['type']=$type;
            $data['third_id']=$openid;
            $data['log_time']=time();
            $data['third_data']=$output;
            UserThirdLogsModel::create($data);
            api_return_json(150, "还未绑定手机号");
        }else{
            $info=UserModel::where('user_id='.$info['user_id'])->find();
            if (empty($info)) {
                api_return_json(150, "还未绑定手机号");
            }else{
                $new_token['token']=UserModel::settoken();
                $new_token['user_id']=$info['user_id'];
                UserModel::update($new_token);
                return UserModel::where('user_id='.$info['user_id'])->field('username,token')->find();
            }
        }
    }
    //判断手机号是否绑定过微信
    public static function third_party_wx($openid,$mobile){
        $info=UserModel::where("mobile='".$mobile."'")->find();
        if (empty($info)){
           return UserWeixinModel::nobinding($openid,$mobile);
        }else{
           return UserWeixinModel::yesbinding($openid,$info['user_id']);
        }

    }

    //手机号已存在
    public static function yesbinding($openid,$user_id)
    {
        //获取微信信息
        $weixin_data=UserThirdLogsModel::where("third_id='".$openid."'")->find();
        $weixin_data=json_decode($weixin_data['third_data'],true);
        $avatar=getImage($weixin_data['headimgurl'],'uploads/weixin/'.date('Ymd'),time().".jpg",1);
        //如果存在并且没有绑定过微信那就关联起来
        $info=UserWeixinModel::where("user_id=".$user_id)->find();
        if (empty($info)){
            $wx_data['wx_openid']=$openid;
            $wx_data['user_id']=$user_id;
            UserWeixinModel::create($wx_data);

            $u_data['avatar']=$avatar['save_path'];
            $u_data['username']=json_encode($weixin_data['nickname']);
            $u_data['token']=UserModel::settoken();
            UserModel::where("user_id='".$user_id."'")->update($u_data);
        }else{
            //如果绑定过微信 那就覆盖以前的微信
            $data['wx_openid']=$openid;
            UserWeixinModel::where("user_id=".$info['user_id'])->update($data);
            //修改用户信息
            $userData['avatar']=$avatar['save_path'];
            $userData['username']=json_encode($weixin_data['nickname']);
            $u_data['token']=UserModel::settoken();
            UserModel::where("user_id='".$user_id."'")->update($userData);
        }
        //然后删除缓存的微信信息
        UserThirdLogsModel::where("third_id='".$openid."'")->delete();
        UserModel::rmAvatarByid($user_id);//删除原图片

        return UserModel::where('user_id='.$user_id)->field('username,token')->find();
    }

    //手机号以前没有绑定过QQ
    public static function nobinding($user_id,$openid,$mobile)
    {
        $request = Request::instance();
        $ip=$request->ip();
        //获取新微信信息
        $weixin_data=UserThirdLogsModel::where("third_id='".$openid."'")->find();
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

        $u_info=$user_id=UserModel::create($data);
        $wx_data['user_id']=$user_id->user_id;
        $wx_data['wx_openid']=$openid;
        UserWeixinModel::create($wx_data);
        //然后删除缓存的QQ信息
        UserThirdLogsModel::where("third_id='".$openid."'")->delete();
        HomeModel::createHome($user_id->user_id, "默认家庭");
        $u_data['username']=$u_info->username;
        $u_data['token']=$u_info->token;
        return $u_data;
    }
}
