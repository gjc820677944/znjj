<?php

namespace app\api\controller\user;

use app\common\model\device\DeviceModelModel;
use app\common\model\home\HomeDeviceProductModel;
use filehelper\FileHelper;

class HomeDevice extends Base
{
    //获取家庭设备列表
    public function index(){
        $input = $this->requset->param();
        if(empty($input['home_id'])){
            api_return_json(361, "家庭ID不能为空");
        }
        $home_id = (int)$input['home_id'];
        $model = new HomeDeviceProductModel();
        $model->where("p.home_id", $home_id);
        if(isset($input['is_gateway']) && $input['is_gateway'] !== ''){
            $is_gateway = (int)$input['is_gateway'];
            $model->where("p.is_gateway", $is_gateway);
        }
        if(!empty($input['tag'])){
            $tag = trim($input['tag']);
            $model->where("p.tag", $tag);
        }
        $field = "p.*, m.model_name, m.model_number, m.model_cover, m.protocol";
        $list = $model->alias("p")->field($field)
            ->join("device_model m", "m.model_id = p.model_id", "left")
            ->select();
        foreach ($list as $k=>$v){
            $v['model_cover'] = FileHelper::helper()->getWebsitePath($v['model_cover']);
            $list[$k] = $v;
        }
        api_return_json(0, $list);
    }

    //绑定设备
    public function bind(){
        $input = $this->requset->param();
        if(empty($input['home_id'])){
            api_return_json(361, "家庭ID不能为空");
        }
        if(empty($input['serial_number'])){
            api_return_json(362, "设备编号不能为空");
        }
        $home_id = $input['home_id'];
        $serial_number = $input['serial_number'];
        //校验设备编号
        $model_id = DeviceModelModel::getIdBySN($serial_number);
        if($model_id <= 0){
            api_return_json(363, "设备编号错误，无法查询到相关设备");
        }
        //模型相关信息
        $model_data = DeviceModelModel::field("is_gateway")->find($model_id);

        //判断该编号设备是否已经存在，存在就更新设备信息，不存在就添加设备信息
        $product_id = (int)HomeDeviceProductModel::where("home_id", $home_id)
            ->where("serial_number", $serial_number)->value("product_id");

        //校验房间是否已有网关设备
        if ($product_id > 0 && $model_data['is_gateway'] === 1){//添加失败
            $where = "home_id = $home_id and is_gateway = 1";
            $count = HomeDeviceProductModel::where($where)->count();
            if($count > 0){
                api_return_json(0, '房间内已有网关设备');
            }
        }
        if($product_id > 0){//更新
            $data = [
                'product_id' => $product_id,
                'tag' => empty($input['tag']) ? '' : trim($input['tag']),
                'model_id' => $model_id,
                'is_gateway' => $model_data['is_gateway'],
            ];
            $result = HomeDeviceProductModel::update($data);
        }
        else{//添加
            $data = [
                'home_id' => $home_id,
                'tag' => empty($input['tag']) ? '' : trim($input['tag']),
                'serial_number' => $serial_number,
                'model_id' => $model_id,
                'is_gateway' => $model_data['is_gateway'],
            ];
            $result = HomeDeviceProductModel::create($data);
        }
        if($result){
            api_return_json(0);
        }
        else{
            api_return_json(365, "绑定失败，请重新尝试");
        }
    }

    //更新设备标签
    public function edit(){
        $input = $this->requset->param();
        if(empty($input['product_id'])){
            api_return_json(371, "产品ID不能为空");
        }
        $data = [
            'product_id' => trim($input['product_id']),
        ];
        if(!empty($data['tag'])){
            $data['tag'] = trim($input['tag']);
        }

        $result = HomeDeviceProductModel::update($data);
        if($result){
            api_return_json(0);
        }
        else{
            api_return_json(373, "更新失败，请重新尝试");
        }
    }

    //删除设备
    public function delete(){
        $product_id = (int)$this->requset->param("product_id");
        if($product_id <= 0){
            api_return_json(371, "产品ID不能为空");
        }
        $result = HomeDeviceProductModel::destroy($product_id);
        if($result){
            api_return_json(0);
        }
        else{
            api_return_json(363, "删除失败，请重新尝试");
        }
    }
}