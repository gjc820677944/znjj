<?php

namespace app\admin\controller\device;

use app\admin\controller\Base;
use app\common\model\admin\AdminOperationLogsModel;
use app\common\model\device\DeviceModelCategoryModel;
use app\common\model\device\DeviceModelModel;
use filehelper\FileHelper;
use think\Request;

class DeviceModel extends Base
{
    public function index()
    {
        $input = $this->request->param();
        $model = new DeviceModelModel();
        if(isset($input['keywords']) && $input['keywords'] !== ''){
            $keywords = $input['keywords'];
            $where = "(m.model_name like '%$keywords%' ".
                    "or m.model_number like '%$keywords%' ".
                    "or m.product_prefix like '%$keywords%' ".
                ")";
            $model->where($where);
        }
        if(isset($input['protocol']) && $input['protocol'] !== ''){
            $protocol = (int)$input['protocol'];
            $model->where("m.protocol", $protocol);
        }
        if(isset($input['cate_id']) && $input['cate_id'] !== ''){
            $cate_id = (int)$input['cate_id'];
            $model->where("m.cate_id", $cate_id);
        }
        if(isset($input['device_type']) && $input['device_type'] !== ''){
            $device_type = (int)$input['device_type'];
            $model->where("m.device_type", $device_type);
        }
        if(isset($input['status']) && $input['status'] !== ''){
            $status = (int)$input['status'];
            $model->where("m.status", $status);
        }
        $list = $model->alias("m")->field("m.*, c.cate_name")
            ->join("device_model_category c", "c.cate_id = m.cate_id", "left")
            ->paginate(null, false, ['query' => $input]);
        foreach ($list as $k=>$v){
            $v['model_cover'] = FileHelper::helper()->getWebsitePath($v['model_cover']);
            $list[$k] = $v;
        }

        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'cate_list' => DeviceModelCategoryModel::lists(),
            'device_type_texts' => DeviceModelModel::$device_type_texts,
            'create_url' => url('create'),
        ];
        return $this->fetch('index', $data);
    }


    public function create()
    {
        $data = [
            'model_id' => 0,
            'model_name' => '',
            'model_cover' => '',
            'model_number' => '',
            'product_prefix' => '',
            'summary' => '',
            'protocol' => 1,
            'cate_id' => 0,
            'is_gateway' => 0,
            'status' => 1,
            'device_type'=>0,
        ];
        $data = [
            'data' => $data,
            'cate_list' => DeviceModelCategoryModel::lists(),
            'protocol_texts' => DeviceModelModel::$protocol_texts,
            'device_type_texts' => DeviceModelModel::$device_type_texts,
            'post_url' => url('save'),
        ];

        return $this->fetch('edit', $data);
    }


    public function save()
    {
        $input = $this->request->post();
        $referer_url = $this->request->param('referer_url');
        unset($input['model_id']);unset($input['referer_url']);
        $this->execValidate('DeviceModel', 'create', $input);

        //存储上传的图片
        $file = Request::instance()->file('model_cover'); //获取上传的头像
        if(!empty($file)){
            $input['model_cover'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'device/model');
        }

        //保存管理员信息
        $result = DeviceModelModel::create($input);
        if($result){
            AdminOperationLogsModel::log("添加智能设备模型[id:".$result->model_id."]");
            $this->success("创建成功", $referer_url);
        }
        else{
            $this->error("创建失败，请重新尝试");
        }
    }


    public function edit($id)
    {
        $data = DeviceModelModel::get($id);
        if(empty($data)){
            $this->error("模型不存在，请重新选择");
        }
        $data['model_cover'] = FileHelper::helper()->getWebsitePath($data['model_cover']);
        $data = [
            'data' => $data,
            'cate_list' => DeviceModelCategoryModel::lists(),
            'protocol_texts' => DeviceModelModel::$protocol_texts,
            'device_type_texts' => DeviceModelModel::$device_type_texts,
            'post_url' => url('update'),
        ];
        return $this->fetch('edit', $data);
    }


    public function update()
    {
        $input = $this->request->post();
        $referer_url = $this->request->param('referer_url');
        unset($input['referer_url']);
        $this->execValidate('DeviceModel', 'edit', $input);

        //存储上传的图片
        $file = Request::instance()->file('model_cover'); //获取上传的头像
        if(!empty($file)){
            $input['model_cover'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'device/model');
        }

        //更新功能点信息
        $result = DeviceModelModel::update($input);
        if($result){
            AdminOperationLogsModel::log("更新智能设备模型[id:".$result->model_id."]");
            $this->success("更新成功", $referer_url);
        }
        else{
            $this->error("更新失败，请重新尝试");
        }
    }

    public function delete(int $id)
    {
        $result = DeviceModelModel::destroy($id);
        if($result){
            AdminOperationLogsModel::log("删除智能设备模型[id:".$id."]");
            api_return_json(0, "删除成功");
        }
        else{
            api_return_json(101, "删除失败，请重新尝试");
        }
    }

    public function changeAttr(){
        $input = $this->request->param();
        if(empty($input['id']) || empty($input['field']) || !isset($input['val'])){
            api_return_json(100, '参数错误，更新失败');
        }
        $id = (int)$input['id'];
        $field = trim($input['field']);
        $val = trim($input['val']);
        $data = [
            'model_id' => $id,
            $field => $val,
        ];
        DeviceModelModel::update($data);
        api_return_json(0);
    }
}
