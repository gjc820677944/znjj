<?php

namespace app\api\controller;

use app\common\model\device\DeviceLogModel;
use app\common\model\device\DeviceModelModel;
use app\common\model\user\UserModel;
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


    //设备操作记录
    function logRecord(){
        $input = $this->requset->param();
        $input['times']=time();                 //操作时间
        $input['operate_people']=UserModel::getTokenId();   //操作用户

        $info=DeviceLogModel::create($input);
        if ($info){
            echo api_return_json(0,"记录成功" );
        }else{
            echo api_return_json(1,"记录失败" );
        }

    }

}
