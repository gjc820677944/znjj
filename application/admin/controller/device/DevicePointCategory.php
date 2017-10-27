<?php

namespace app\admin\controller\device;

use app\admin\controller\Base;
use app\common\model\device\DevicePointCategoryModel;

class DevicePointCategory extends Base
{
    public function index(){
        $list = DevicePointCategoryModel::order("sort_by desc")->select();
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
        $result = DevicePointCategoryModel::create($input);
        if($result){
            $this->success("创建成功", $referer_url);
        }
        else{
            $this->error("创建失败，请重新尝试");
        }
    }


    public function edit($id)
    {
        $data = DevicePointCategoryModel::get($id);
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
        $result = DevicePointCategoryModel::update($input);
        if($result){
            $this->success("更新成功", $referer_url);
        }
        else{
            $this->error("更新失败，请重新尝试");
        }
    }

    public function delete(int $id)
    {
        $result = DevicePointCategoryModel::destroy($id);
        if($result){
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
        DevicePointCategoryModel::update($data);
        api_return_json(0);
    }
}
