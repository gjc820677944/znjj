<?php
namespace app\common\model\user;
use filehelper\FileHelper;
use app\common\model\user\UserThirdLogsModel;
use app\common\model\home\HomeModel;
use think\Model;
use think\Request;
use think\Db;
class UserQQModel extends Model
{
    protected $name = "user_qq";
    public static function QQlogin($type,$access_token,$openid,$appid)
    {
        $where['qq_openid']=$openid;
        $info=UserQQModel::where($where)->find();
        //获取用户微信息
        $url = "https://graph.qq.com/user/get_user_info?access_token=" . $access_token . "&oauth_consumer_key=".$appid."&openid=" . $openid . "&lang=zh_CN ";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $output = json_decode($output, true);
        if (!isset($output['nickname'])){
            api_return_json(1, "获取QQ信息失败");
        }
        $output['headimgurl']=$output['figureurl_qq_1'];
        $output=json_encode($output);
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
            if (empty($info)){
                api_return_json(150, "还未绑定手机号");
            }else{
                $where['qq_openid']=$openid;
                return  $data= Db::name('user_qq')->alias('uq')->where($where)
                    ->field("u.user_id,u.username,ifnull(u.mobile,'') as mobile,u.avatar,u.token")
                    ->join('user u','u.user_id=uq.user_id','left')
                    ->find();
            }
        }
    }

    //判断手机号是否绑定过QQ
    public static function third_party_qq($openid,$mobile){
        $info=UserModel::where("mobile='".$mobile."'")->find();
        if (empty($info)){
           return UserQQModel::nobinding($openid,$mobile);
        }else{
           return UserQQModel::yesbinding($openid,$info['user_id']);
        }

    }

    //手机号以前绑定过 QQ
    public static function yesbinding($openid,$user_id)
    {

        //获取新QQ信息
        $weixin_data=UserThirdLogsModel::where("third_id='".$openid."'")->find();
        $weixin_data=json_decode($weixin_data['third_data'],true);
        $avatar=getImage($weixin_data['headimgurl'],'uploads/weixin/'.date('Ymd'),time().".jpg",1);
        //如果存在并且没有绑定过QQ那就关联起来
        $info=UserQQModel::where("user_id=".$user_id)->find();
        if (empty($info)){
            $wx_data['qq_openid']=$openid;
            $wx_data['user_id']=$user_id;
            UserQQModel::create($wx_data);
            $u_data['avatar']=$avatar['save_path'];
            $u_data['username']=json_encode($weixin_data['nickname']);
            UserModel::where("user_id='".$user_id."'")->update($u_data);
        }else{
            //如果绑定过QQ 那就覆盖以前的QQ
            $data['qq_openid']=$openid;
            UserQQModel::where("user_id=".$info['user_id'])->update($data);
            //修改用户信息
            $userData['avatar']=$avatar['save_path'];
            $userData['username']=json_encode($weixin_data['nickname']);
            UserModel::where("user_id='".$user_id."'")->update($userData);
        }
        //然后删除缓存的QQ信息
        UserThirdLogsModel::where("third_id='".$openid."'")->delete();
        UserModel::rmAvatarByid($user_id);//删除原图片
        return UserModel::where('user_id='.$user_id)->field('username,token')->find();
    }

    //手机号以前没有绑定过QQ
    public static function nobinding($openid,$mobile)
    {
        $request = Request::instance();
        $ip=$request->ip();
        //获取新QQ信息
        $QQ_data=UserThirdLogsModel::where("third_id='".$openid."'")->find();
        $QQ_data=json_decode($QQ_data['third_data'],true);
        $avatar=getImage($QQ_data['headimgurl'],'uploads/weixin/'.date('Ymd'),time().".jpg",1);

        $data['username']=json_encode($QQ_data['nickname']).rand(1,5);
        $data['mobile']=$mobile;
        $data['avatar']=$avatar['save_path'];;
        $data['last_login_ip']=$ip;
        $data['last_login_time']=time();
        $data['reg_ip']=$ip;
        $data['reg_time']=time();
        $data['token']=UserModel::settoken();
        $u_info=UserModel::create($data);

        $data_qq['user_id']=$u_info->user_id;
        $data_qq['qq_openid']=$openid;
        UserQQModel::insert($data_qq);
        //然后删除缓存的QQ信息
        UserThirdLogsModel::where("third_id='".$openid."'")->delete();
        HomeModel::createHome($u_info->user_id, "默认家庭");
        $u_data['username']=$u_info->username;
        $u_data['token']=$u_info->token;
        return $u_data;
    }

}
