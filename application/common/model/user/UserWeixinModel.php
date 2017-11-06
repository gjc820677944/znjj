<?php

namespace app\common\model\user;

use think\Model;

class UserWeixinModel extends Model
{
    protected $name = "user_third_logs";
    public static function weixinlogin($type,$access_token,$openid,$unionid)
    {
        $where['third_id']=$openid;
        $info=Db::name('user_weixin')->where($where)->find();
        //获取用户微信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $access_token . "&openid=" . $openid . "&lang=zh_CN ";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
//        $output = json_decode($output, true);
        curl_close($ch);
        if (empty($info)){
            $data['type']=$type;
            $data['third_id']=$openid;
            $data['log_time']=time();
            $data['third_data']=$output;
            UserWeixinModel::create($data);
            api_return_json(125, "还未绑定手机号，请绑定手机号");
        }else{
            $where['wx_openid']=$openid;
            return  $data= Db::name('user_weixin')->alias('uw')->where($where)
                ->field("u.user_id,u.username,ifnull(u.mobile,'') as mobile,u.avatar,u.token")
                ->join('user u','u.user_id=uw.user_id','left')
                ->find();
        }
    }

}
