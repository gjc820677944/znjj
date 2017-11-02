<?php

namespace app\admin\controller\device;

use app\admin\controller\Base;
use app\common\model\admin\AdminOperationLogsModel;
use app\common\model\device\DeviceModelCategoryModel;
use app\common\model\device\DeviceModelModel;

class DeviceModelCategory extends Base
{
    public function index(){
        $list = DeviceModelCategoryModel::alias("c")
            ->field("c.*, count(m.model_id) as model_count")
            ->join("device_model m", "m.cate_id = c.cate_id", "left")
            ->group("c.cate_id")
            ->order("c.sort_by desc, c.cate_id asc")->select();;
        $data = [
            'list' => $list
        ];
        return $this->fetch('', $data);
    }

    public function create()
    {
        $data = [
            'cate_id' => 0,
            'cate_name' => '',
            'sort_by' => 0,
        ];
        $data = [
            'data' => $data,
            'post_url' => url('save'),
        ];
        return $this->fetch('edit', $data);
    }


    public function save()
    {
        $input = $this->request->post();
        $referer_url = $this->request->param('referer_url');
        unset($input['ad_id']);unset($input['referer_url']);
        if(empty($input['cate_name'])){
            $this->error("分类名字不能为空");
        }
        //保存分类信息
        $result = DeviceModelCategoryModel::create($input);
        if($result){
            AdminOperationLogsModel::log(
                'DeviceModelCategory',
                'create',
                ['cate_id'=>$result->cate_id]
            );
            $this->success("创建成功", $referer_url);
        }
        else{
            $this->error("创建失败，请重新尝试");
        }
    }


    public function edit($id)
    {
        $data = DeviceModelCategoryModel::get($id);
        if(empty($data)){
            $this->error("分类不存在，请重新选择");
        }
        $data = [
            'data' => $data,
            'post_url' => url('update'),
        ];
        return $this->fetch('edit', $data);
    }


    public function update()
    {
        $input = $this->request->post();
        $referer_url = $this->request->param('referer_url');
        unset($input['referer_url']);

        if(empty($input['cate_id'])){
            $this->error("分类ID不能为空");
        }
        if(empty($input['cate_name'])){
            $this->error("分类名字不能为空");
        }
        //更新分类信息
        $result = DeviceModelCategoryModel::update($input);
        if($result){
            AdminOperationLogsModel::log(
                'DeviceModelCategory',
                'update',
                ['cate_id'=>$result->cate_id]
            );
            $this->success("更新成功", $referer_url);
        }
        else{
            $this->error("更新失败，请重新尝试");
        }
    }

    public function delete(int $id)
    {
        $count = DeviceModelModel::where("cate_id", $id)->count();
        if($count > 0){
            api_return_json(0, "分类下包含模型数据，无法删除");
        }

        $result = DeviceModelCategoryModel::destroy($id);
        if($result){
            AdminOperationLogsModel::log(
                'DeviceModelCategory',
                'update',
                ['cate_id'=>$id]
            );
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
            'cate_id' => $id,
            $field => $val,
        ];
        DeviceModelCategoryModel::update($data);
        api_return_json(0);
    }
}
