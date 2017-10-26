<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/2/28
 * Time: 11:48
 */
namespace app\common\validate;

use app\common\model\config\WebConfigModel;
use think\Validate;

class WebConfig extends Validate
{
    protected $rule =   [
        'conf_id' => 'require|gt:0',
        'conf_name' => 'require|isUniqueName',
        'conf_key' => 'require|isUniqueConfigKey',
        'conf_value' => 'require',
        'type' => 'require',
    ];

    protected $message  =   [
        'conf_id.require' => '配置项ID不能为空',
        'conf_id.gt' => '配置项ID必须大于0',
        'conf_name.require' => '配置项名称不能为空',
        'conf_name.isUniqueName' => '配置项名称已存在',
        'conf_key.require' => '配置项键不能为空',
        'conf_key.isUniqueConfigKey' => '配置项键已存在',
        'conf_value' => '配置项值不能为空',
        'type' => '文本类型不能为空',
    ];

    protected $scene = [
        'create' => ['conf_name', 'conf_key', 'conf_value', 'type'],
        'edit' => ['conf_id', 'conf_name', 'conf_key', 'conf_value', 'type'],
        'delete' => ['conf_id'],
    ];

    protected function isUniqueName($value, $rule, $data){
        $where = "conf_name = '$value'";
        if(isset($data['conf_id']) && (int)$data['conf_id'] > 0){
            $id = (int)$data['conf_id'];
            $where .= " and conf_id != $id";
        }
        $count = WebConfigModel::where($where)->count();
        return $count ? false : true;
    }

    protected function isUniqueConfigKey($value, $rule, $data){
        $where = "conf_key = '$value'";
        if(isset($data['conf_id']) && (int)$data['conf_id'] > 0){
            $id = (int)$data['conf_id'];
            $where .= " and conf_id != $id";
        }
        $count = WebConfigModel::where($where)->count();
        return $count ? false : true;
    }
}