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


    //记录设备操作
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

    //设备操作日志列表
    function logRecordList(){
        $input = $this->requset->param();

        $type=$input['type'];
        if ($type=='all'){
            $data=DeviceLogModel::where('home_id='.$input['home_id'])->select();
        }else{
            $data=DeviceLogModel::where('home_id='.$input['home_id'])->order('log_id desc')->limit('0,10')->select();
        }
        echo api_return_json(0,$data );
    }

}
