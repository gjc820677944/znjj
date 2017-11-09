<?php
namespace umeng;

class Umeng{

    /**
     * 向指定设备推送消息
     * @param string $device_token 设备ID
     * @param string $message 消息内容
     * @param int $type 设备类型 1-Android 2-IOS
     * @return bool
     */
    public static function sendUnicast($device_token, $message, $type = 1){
        if($type === 1){
            $result = UmengAndroid::instance()
                ->sendAndroidUnicast($device_token, $message,'');
        }
        else{
            $result = UmengIOS::instance()
                ->sendIOSUnicast($device_token, $message);
        }
        return $result;
    }
}