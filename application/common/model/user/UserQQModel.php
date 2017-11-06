<?php
namespace app\common\model\user;
use filehelper\FileHelper;
use think\Model;
use think\Request;
use think\Db;
class UserQQModel extends Model
{
    protected $name = "smart_user_third_logs";
    public static function QQlogin($type,$access_token,$openid,$appid)
    {
        $where['wx_openid']=$openid;
        $info=Db::name('user_weixin')->where($where)->find();
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
        json_encode($output);
        curl_close($ch);
        if (empty($info)){
            $data['type']=$type;
            $data['third_id']=$openid;
            $data['log_time']=time();
            $data['third_data']=$output;
            UserWeixinModel::create($data);
            api_return_json(150, "还未绑定手机号");
        }else{
            $where['wx_openid']=$openid;
            return  $data= Db::name('user_weixin')->alias('uw')->where($where)
                ->field("u.user_id,u.username,ifnull(u.mobile,'') as mobile,u.avatar,u.token")
                ->join('user u','u.user_id=uw.user_id','left')
                ->find();
        }


    }

}
