<?php

namespace app\common\model\device;

use think\Model;

class DeviceModelModel extends Model
{
    protected $name = "device_model";
    protected $autoWriteTimestamp = true;

    public static $protocol_texts = [
        1 => 'zigbee',
        2 => 'wifi',
        3 => '蓝牙',
    ];

    public function getProtocolNameAttr($value, $data){
        $protocol = $data['protocol'];
        return self::$protocol_texts[$protocol];
    }

    protected function getStatusTextAttr($val, $data){
        $status = $data['status'];
        $texts = [1=>'已开启', 4=>'已关闭'];
        return $texts[$status];
    }
}
