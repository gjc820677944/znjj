<?php
namespace app\common\validate;

use app\common\model\device\DeviceModelModel;
use think\Validate;

class DeviceModel extends Validate
{
    protected $rule = [
        'model_id' => 'require|gt:0',
        'model_name' => 'require',
        'model_number' => 'require|isUniqueNumber',
        'product_prefix' => 'require|isUniquePrefix',
        'protocol' => 'require',
        'cate_id' => 'require|gt:0',
    ];
    
    protected $message = [
        'model_id' => '模型ID必须大于0',
        'model_name' => '模型名字不能为空',
        'model_number.require' => '模型编号不能为空',
        'model_number.isUniqueNumber' => '模型编号已存在',
        'product_prefix.require' => '产品编号前缀不能为空',
        'product_prefix.isUniquePrefix' => '产品编号前缀已存在',
        'protocol' => '通讯协议不能为空',
        'cate_id' => '产品ID必须大于0',
    ];
    
    protected $scene = [
        'create' => ['model_name', 'model_number', 'product_prefix', 'protocol', 'cate_id'],
        'edit' => ['model_id', 'model_name', 'model_number', 'product_prefix', 'protocol', 'cate_id'],
    ];

    protected function isUniqueNumber($value, $rule, $input){
        $where = "model_number = '$value'";
        if(isset($input['model_id']) && (int)$input['model_id'] > 0){ //更新操作
            $model_id = $input['model_id'];
            $where .= " and model_id != $model_id";
        }
        $count = DeviceModelModel::where($where)->count();
        return $count ? false : true;
    }

    protected function isUniquePrefix($value, $rule, $input){
        $where = "product_prefix = '$value'";
        if(isset($input['model_id']) && (int)$input['model_id'] > 0){ //更新操作
            $model_id = $input['model_id'];
            $where .= " and model_id != $model_id";
        }
        $count = DeviceModelModel::where($where)->count();
        return $count ? false : true;
    }
}