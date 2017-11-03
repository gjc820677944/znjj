<?php

namespace app\admin\controller\device;

use app\admin\controller\Base;
use app\common\model\admin\AdminOperationLogsModel;
use app\common\model\device\DeviceModelModel;
use app\common\model\device\DeviceModelPointModel;
use app\common\model\device\DevicePointCategoryModel;
use app\common\model\device\DevicePointModel;

class DeviceModelPoint extends Base
{
    public function index(int $model_id = 0)
    {
        if($model_id <= 0){
            $this->error("模型ID错误");
        }
        $device_model = DeviceModelModel::where("model_id", $model_id)
            ->field("model_id, model_name, model_number")
            ->find();
        if(empty($device_model)){
            $this->error("模型数据不存在");
        }

        $field = "mp.*, p.point_name, p.status point_status, pc.cate_name point_cate_name";
        $where = "mp.model_id = $model_id";
        $model = new DeviceModelPointModel();
        $list = $model->alias("mp")->field($field)
            ->join("device_point p", "p.point_id = mp.point_id", "left")
            ->join("device_point_category pc", "pc.cate_id = p.cate_id", "left")
            ->where($where)->order("mp.sort_by desc")->select();
        $data = [
            'list' => $list,
            'device_model' => $device_model,
            'model_id' => $model_id,
        ];
        return $this->fetch('index', $data);
    }

    public function modalBody(int $model_id = 0){
        $input = $this->request->param();
        if($model_id <= 0){
            api_return_json(100, '模型ID错误');
        }
        $point_ids = DeviceModelPointModel::where("model_id", $model_id)
            ->column("point_id");
        if(empty($point_ids)){
            $point_ids = [0];
        }

        $model = new DevicePointModel();
        $model->where("point_id", "not in", $point_ids);
        $model->where("status", 1);
        if (isset($input['cate_id']) && $input['cate_id'] !== ''){
            $cate_id = (int)$input['cate_id'];
            $model->where("p.cate_id", $cate_id);
        }
        if(isset($input['keywords']) && $input['keywords'] !== ''){
            $keywords = $input['keywords'];
            $model->where("p.point_name like '%$keywords%'");
        }

        $list = $model->alias("p")->field("p.point_id, p.point_name, c.cate_name")
            ->join("device_point_category c", "c.cate_id = p.cate_id", "left")
            ->paginate(10, false, ['query' => $input]);
        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'model_id' => $model_id,
            'cate_list' => DevicePointCategoryModel::lists(),
        ];
        $html = $this->fetch('modal_body', $data);
        api_return_json(0, $html);
    }

    public function save(int $model_id = 0){
        $input = $this->request->param();
        if(!isset($input['point_ids']) || empty($input['point_ids'])){
            api_return_json(101, '请选择功能点');
        }
        $list = [];
        $point_ids = $input['point_ids'];
        foreach ($point_ids as $point_id){
            $list[] = [
                'model_id' => $model_id,
                'point_id' => $point_id,
            ];
        }
        $result = (new DeviceModelPointModel())->insertAll($list);
        if($result){
            AdminOperationLogsModel::log("为智能设备绑定功能点[id:".$model_id."]");
            api_return_json(0, '绑定成功');
        }
        else{
            api_return_json(11, '绑定失败，请重新尝试');
        }
    }

    public function delete(int $model_id, int $point_id)
    {
        $result = DeviceModelPointModel::where("model_id", $model_id)
            ->where("point_id", $point_id)->delete();
        if($result){
            AdminOperationLogsModel::log("删除智能设备绑定功能点[id:".$model_id."]");
            api_return_json(0, "删除成功");
        }
        else{
            api_return_json(101, "删除失败，请重新尝试");
        }
    }

    public function changeAttr(int $model_id){
        $input = $this->request->param();
        if(empty($input['id']) || empty($input['field']) || !isset($input['val'])){
            api_return_json(100, '参数错误，更新失败');
        }
        $id = (int)$input['id'];
        $field = trim($input['field']);
        $val = trim($input['val']);
        $data = [
            'model_id' => $model_id,
            'point_id' => $id,
            $field => $val,
        ];
        DeviceModelPointModel::update($data);
        api_return_json(0);
    }
}
