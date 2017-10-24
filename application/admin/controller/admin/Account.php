<?php

namespace app\admin\controller\admin;

use app\admin\controller\Base;
use app\common\model\admin\AdminModel;

class Account extends Base
{
    public function index()
    {

        $input = $this->request->param();
        $model = new AdminModel();
        if(isset($input['keywords']) && $input['keywords'] !== ''){
            $keywords = $input['keywords'];
            $model->where("ad_account like '%$keywords%'")
                ->whereOr("ad_name like '%$keywords%'")
                ->whereOr("email like '%$keywords%'");
        }
        if(isset($input['status']) && $input['status'] !== ''){
            $status = (int)$input['status'];
            $model->where("status", $status);
        }
        $list = $model->paginate(null, false, [
                'query' => $input,
            ]);
        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'create_url' => url('create'),
        ];
        return $this->fetch('index', $data);
    }


    public function create()
    {
        $data = [
            'ad_id' => 0,
            'ad_account' => '',
            'password' => '',
            'avatar' => '',
            'ad_name' => '',
            'email' => '',
            'status' => 0,
        ];
        $data = [
            'data' => $data,
            'post_url' => url('save'),
        ];
        return $this->fetch('edit', $data);
    }


    public function save()
    {

    }


    public function edit($id)
    {
        $data = AdminModel::get($id);
        if(empty($data)){
            $this->error("管理员不存在，请重新选择");
        }
        $data = [
            'data' => $data,
        ];
        return $this->fetch('edit', $data);
    }


    public function update()
    {

    }

    public function delete($id)
    {
        $result = AdminModel::destroy($id);
        if($result){
            $this->success("删除成功");
        }
        else{
            $this->error("删除失败，请重新尝试");
        }
    }
}
