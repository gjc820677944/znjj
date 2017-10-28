<?php

namespace app\common\model\device;

use filehelper\FileHelper;
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

    protected static function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        self::event("before_update", function($model){
            if(isset($model->model_id) && isset($model->model_cover)){
                DeviceModelModel::rmCoverByid($model->model_id);
            }
        });
        self::event("before_delete", function($model){
            if(isset($model->model_id)){
                DeviceModelModel::rmCoverByid($model->model_id);
            }
        });
    }

    /**
     * 根据ID删除模型图标
     * @param int $id 管理员ID
     */
    public static function rmCoverByid($id){
        $vo = DeviceModelModel::field("model_cover")->find($id);
        if($vo && $vo['model_cover']){
            FileHelper::helper()->unlink($vo['model_cover']);
        }
    }
}
