<?php

namespace app\admin\controller\admin;

use app\admin\controller\Base;
use app\common\model\admin\AdminModel;
use app\common\model\admin\AdminOperationLogsModel;
use app\common\model\admin\AdminRoleModel;
use app\common\validate\ValidateHelper;
use filehelper\FileHelper;
use helper\Helper;
use think\Request;

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

        foreach ($list as $k=>$v){
            if($v['avatar'] === ''){
                $v['avatar'] = AdminModel::getDefaultAvatar();
            }
            else{
                $v['avatar'] = FileHelper::helper()->getWebsitePath($v['avatar']);
            }
            $role_names = AdminModel::getRoleNames($v['ad_id']);
            $v['role_names'] = implode(', ', $role_names);
            $list[$k] = $v;
        }
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
            'status' => 1,
        ];
        $data = [
            'data' => $data,
            'role'  =>  AdminRoleModel::where('status=1')->select(),
            'js'    =>  array(),
            'post_url' => url('save'),
        ];

        return $this->fetch('edit', $data);
    }


    public function save()
    {
        $input = $this->request->post();

        if (isset($input['role_id'])){
            $input['role_id']=implode(',',$input['role_id']);
        }

        $referer_url = $this->request->param('referer_url');
        unset($input['ad_id']);unset($input['referer_url']);
        $this->execValidate('Admin', 'create', $input);
        //存储上传的图片
        $file = Request::instance()->file('avatar'); //获取上传的头像
        if(!empty($file)){
            $input['avatar'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'admin/avatar');
        }
        //保存管理员信息
        $result = AdminModel::createOrUpdate(0, $input);
        if($result){
            AdminOperationLogsModel::log("添加控制台管理员[id:".$result->ad_id."]");
            $this->success("创建成功", $referer_url);
        }
        else{
            $this->error("创建失败，请重新尝试");
        }
    }


    public function edit($id)
    {
        $data = AdminModel::get($id);
        if(empty($data)){
            $this->error("管理员不存在，请重新选择");
        }
        $data['avatar'] = FileHelper::helper()->getWebsitePath($data['avatar']);
        $js=explode(',',$data['role_id']);

        $data = [
            'data' => $data,
            'role'  =>  AdminRoleModel::where('status=1')->select(),
            'js'    =>  $js,
            'post_url' => url('update'),
        ];
        return $this->fetch('edit', $data);
    }


    public function update()
    {
        $input = $this->request->post();
        if (isset($input['role_id'])){
            $input['role_id']=implode(',',$input['role_id']);
        }
        $referer_url = $this->request->param('referer_url');
        unset($input['referer_url']);
        if(empty($input['password'])) unset($input['password']);
        $this->execValidate('Admin', 'edit', $input);
        //存储上传的图片
        $file = Request::instance()->file('avatar'); //获取上传的头像
        if(!empty($file)){
            $input['avatar'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'admin/avatar');
        }

        $ad_id = (int)$input['ad_id'];
        if($ad_id === ADMINISTRATOR_ID){
            unset($input['status']); //超级管理员不能修改状态
        }
        //更新管理员信息
        $result = AdminModel::createOrUpdate($ad_id, $input);
        if($result){
            AdminOperationLogsModel::log("更新控制台管理员[id:".$result->ad_id."]");
            $this->success("更新成功", $referer_url);
        }
        else{
            $this->error("更新失败，请重新尝试");
        }
    }

    public function delete(int $id)
    {
        if($id === ADMINISTRATOR_ID){
            $this->error("超级管理员无法删除");
        }
        $result = AdminModel::destroy($id);
        if($result){
            AdminOperationLogsModel::log("删除控制台管理员[id:".$id."]");
            api_return_json(0, "删除成功");
        }
        else{
            api_return_json(101, "删除失败，请重新尝试");
        }
    }
}
