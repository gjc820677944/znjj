<?php

namespace app\api\controller;

use app\common\model\device\DeviceModelModel;
use filehelper\FileHelper;

class DeviceModel extends Base
{
    public function index(){
        $input = $this->requset->param();
        $model = new DeviceModelModel();
        if(isset($input['is_geteway']) && $input['is_geteway'] !== ''){
            $is_geteway = (int)$input['is_geteway'];
            $model->where('is_geteway', $is_geteway);
        }
        $model->where("status", 1);
        $field = "model_id, model_name, model_number, model_cover, product_prefix, protocol";
        $list = $model->field($field)->select();
        foreach ($list as $k=>$v){
            $v['model_cover'] = FileHelper::helper()->getWebsitePath($v['model_cover']);
            $list[$k] = $v;
        }
        api_return_json(0, $list);
    }


}
