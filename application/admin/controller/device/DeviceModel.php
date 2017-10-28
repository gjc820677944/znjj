<?php

namespace app\admin\controller\device;

class DeviceModel extends Base
{
    public function index()
    {
        $input = $this->request->param();
        $model = new DevicePointModel();
        if(isset($input['keywords']) && $input['keywords'] !== ''){
            $keywords = $input['keywords'];
            $model->where("p.point_name like '%$keywords%'");
        }
        if(isset($input['tag']) && $input['tag'] !== ''){
            $tag = $input['tag'];
            $model->where("p.tag", $tag);
        }
        if(isset($input['cate_id']) && $input['cate_id'] !== ''){
            $cate_id = (int)$input['cate_id'];
            $model->where("p.cate_id", $cate_id);
        }
        if(isset($input['status']) && $input['status'] !== ''){
            $status = (int)$input['status'];
            $model->where("p.status", $status);
        }
        $list = $model->alias("p")->field("p.*, c.cate_name")
            ->join("device_point_category c", "c.cate_id = p.cate_id", "left")
            ->paginate(null, false, ['query' => $input]);
        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'cate_list' => DevicePointCategoryModel::lists(),
            'create_url' => url('create'),
        ];
        return $this->fetch('index', $data);
    }


    public function create()
    {
        $data = [
            'point_id' => 0,
            'point_name' => '',
            'tag' => '',
            'summary' => '',
            'point_class' => '',
            'sort_by' => 0,
            'status' => 1,
            'cate_id' => 0,
        ];
        $data = [
            'data' => $data,
            'cate_list' => DevicePointCategoryModel::lists(),
            'post_url' => url('save'),
        ];
        return $this->fetch('edit', $data);
    }


    public function save()
    {
        $input = $this->request->post();
        $referer_url = $this->request->param('referer_url');
        unset($input['point_id']);unset($input['referer_url']);
        $this->execValidate('DevicePoint', 'create', $input);
        //保存管理员信息
        $result = DevicePointModel::create($input);
        if($result){
            $this->success("创建成功", $referer_url);
        }
        else{
            $this->error("创建失败，请重新尝试");
        }
    }


    public function edit($id)
    {
        $data = DevicePointModel::get($id);
        if(empty($data)){
            $this->error("功能点不存在，请重新选择");
        }
        $data = [
            'data' => $data,
            'cate_list' => DevicePointCategoryModel::lists(),
            'post_url' => url('update'),
        ];
        return $this->fetch('edit', $data);
    }


    public function update()
    {
        $input = $this->request->post();
        $referer_url = $this->request->param('referer_url');
        unset($input['referer_url']);
        if(empty($input['password'])) unset($input['password']);
        $this->execValidate('DevicePoint', 'edit', $input);

        //更新功能点信息
        $result = DevicePointModel::update($input);
        if($result){
            $this->success("更新成功", $referer_url);
        }
        else{
            $this->error("更新失败，请重新尝试");
        }
    }

    public function delete(int $id)
    {
        $result = DevicePointModel::destroy($id);
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
            'point_id' => $id,
            $field => $val,
        ];
        DevicePointModel::update($data);
        api_return_json(0);
    }
}
