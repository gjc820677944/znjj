<?php
namespace app\common\validate;

use app\common\model\admin\AdminModel;
use helper\Check;
use think\Validate;

class DevicePoint extends Validate
{
    protected $rule = [
        'point_id' => 'require|gt:0',
        'point_name' => 'require',
        'point_class' => 'require',
        'cate_id' => 'require|gt:0',
    ];
    
    protected $message = [
        'point_id' => '功能点ID必须大于0',
        'point_name' => '功能点名字不能为空',
        'point_class' => '功能点对应类不能为空',
        'cate_id' => '请选择功能点分类',
    ];
    
    protected $scene = [
        'create' => ['point_name', 'cate_id'],
        'edit' => ['point_id', 'point_name', 'cate_id'],
    ];
}