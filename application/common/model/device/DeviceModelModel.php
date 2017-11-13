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

    //字典
    public static $Dictionaries=[
        [   'name'  =>  'deviceType',
            'options'=>[
            ['id'=>1, 'name'=>'门'],
            ['id'=>2, 'name'=>'锁'],
            ['id'=>3, 'name'=>'摄像头'],
        ],],
        [   'name'  =>  'tag',
            'options'    => [
            ['id'=>1,'name'=>'大厅'],
            ['id'=>2,'name'=>'卧室'],
            ['id'=>3,'name'=>'厨房'],
            ['id'=>4,'name'=>'走廊'],
            ['id'=>5,'name'=>'次卧'],
            ['id'=>6,'name'=>'卫生间'],
        ],],
    ];

    public static $device_type_texts = [
                ['id'=>1, 'name'=>'门'],
                ['id'=>2, 'name'=>'锁'],
                ['id'=>3, 'name'=>'摄像头'],
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

    public function getDeviceTypeTextAttr($value, $data){
        $device_type = $data['device_type'];
        foreach (self::$device_type_texts as $v){
            if($v['id'] === $device_type){
                return $v['name'];
            }
        }
        return '';
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

    /**
     * 根据设备编号，获取设备的模型ID
     * @param int 查询条件
     * @return int 为0时表示未查询到相关的模型ID
     */
    public static function getIdBySN($serial_number, $where = null){
        if($serial_number === ''){
            return 0;
        }
        $model = new DeviceModelModel();
        if($where !== null){
            $model->where($where);
        }
        $product_prefixs = $model->column("product_prefix"); //获取符合条件的产品编号前缀
        $model_id = 0;
        foreach ($product_prefixs as $product_prefix) {
            $length = strlen($product_prefix);
            if(substr($serial_number, 0, $length) === $product_prefix){
                $model_id = DeviceModelModel::where("product_prefix", $product_prefix)
                    ->value("model_id");
                break;
            }
        }
        return $model_id;
    }
}
