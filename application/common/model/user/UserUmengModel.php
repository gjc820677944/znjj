<?php

namespace app\common\model\user;

use helper\Helper;
use think\Model;

class UserUmengModel extends Model
{
    protected $name = "user_umeng";
    /**
     * 根据用户ID创建用户Loginkey
     * @param data 用户ID
     * @return string 用户loginkey
     */
    public static function saveDeviceToken($user_id, $device_token){
        //保存设备ID
        $device_data = [
            'user_id' => $user_id,
        ];
        $system = Helper::getDeviceType();
        if($system === 'ios'){
            $device_data['ios_device_token'] = $device_token;
        }
        elseif($system === 'android'){
            $device_data['android_device_token'] = $device_token;
        }
        if(UserUmengModel::find($user_id)){
            $res = UserUmengModel::update($device_data);
        }
        else{
            $res = UserUmengModel::create($device_data);
        }
        return $res ? true : false;
    }
}
