<?php

namespace app\common\model\device;

use think\Model;

class DevicePointModel extends Model
{
    protected $name = "device_point";
    protected $autoWriteTimestamp = true;

    protected function getStatusTextAttr($val, $data){
        $status = $data['status'];
        $texts = [1=>'已开启', 4=>'已关闭'];
        return $texts[$status];
    }
}
