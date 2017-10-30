<?php

namespace app\api\controller\user;

use app\common\model\device\DeviceModelModel;
use app\common\model\home\HomeDeviceProductModel;
use app\common\model\home\HomeModel;

class Home extends Base
{
    //创建家庭
    public function create(){
        $input = $this->requset->param();
        if(!isset($input['home_name']) || $input['home_name'] === ''){
            api_return_json(301, "家庭名字不能为空");
        }
        if(!isset($input['serial_number']) || $input['serial_number'] === ''){
            api_return_json(302, "网关设备编号不能为空");
        }
        $home_name = trim($input['home_name']);
        $serial_number = trim($input['serial_number']);
        $where = "is_geteway = 1 and status = 1";
        $model_id = DeviceModelModel::getIdBySN($serial_number, $where);
        if($model_id <= 0){
            api_return_json(303, "设备编号错误，查询关联模型失败");
        }

        //添加房间与家庭
        $model = new HomeModel();
        $model->startTrans();
        //插入房间
        $home_data = [
            'creater_id' => $this->user_id,
            'home_name' => $home_name,
        ];
        $home = HomeModel::create($home_data);
        if(empty($home)){
            $model->rollback();
            api_return_json(304, "房间创建失败，请重新尝试");
        }
        //插入网关设备
        $product_data = [
            'home_id' => $home->home_id,
            'serial_number' => $serial_number,
            'model_id' => $model_id,
            'is_gateway' => 1,
        ];
        $product = HomeDeviceProductModel::create($product_data);
        if(empty($home)){
            $model->rollback();
            api_return_json(305, "网关创建失败，请重新尝试");
        }
        $model->commit();
        api_return_json(0);
    }
}