<?php

namespace app\common\model\device;

use think\Model;

class DeviceModelPointModel extends Model
{
    protected $name = "device_model_point";

    protected function getPointStatusTextAttr($val, $data){
        $status = $data['point_status'];
        $texts = [1=>'已开启', 4=>'已关闭'];
        return $texts[$status];
    }
}
