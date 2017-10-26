<?php

namespace app\admin\controller;

use app\common\model\admin\AdminModel;
use app\common\validate\ValidateHelper;
use filehelper\FileHelper;
use think\Request;

class Profile extends Base
{
    public function index(){
        $admin = AdminModel::get($this->ad_id);
        $admin['avatar'] = FileHelper::helper()->getWebsitePath($admin['avatar']);
        $data = [
            'data' => $admin,
            'post_url' => url('update'),
        ];
        return $this->fetch('index', $data);
    }

    public function update(){
        $input = $this->request->post();
        $input['ad_id'] = $ad_id = $this->ad_id;
        if(empty($input['password'])) unset($input['password']);
        $result = ValidateHelper::execValidate('Admin', 'personal', $input);
        if($result !== true){
            return $this->error($result);
        }
        //存储上传的图片
        $file = Request::instance()->file('avatar'); //获取上传的头像
        if(!empty($file)){
            $input['avatar'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'admin/avatar');
        }

        //更新管理员信息
        $result = AdminModel::createOrUpdate($ad_id, $input);
        if($result){
            $this->success("更新成功");
        }
        else{
            $this->error("更新失败，请重新尝试");
        }
    }
}
