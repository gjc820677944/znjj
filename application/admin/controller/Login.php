<?php

namespace app\admin\controller;

use app\common\model\admin\AdminAuthRuleModel;
use app\common\model\admin\AdminLogsModel;
use app\common\model\admin\AdminModel;
use app\common\model\admin\AdminRoleModel;
use app\common\validate\Admin;
use app\common\validate\ValidateHelper;
use helper\Check;
use think\Controller;

class Login extends Controller
{
    public function index(){
        $data = [
            'post_url' => url('check'),
        ];
        return $this->fetch("index", $data);
    }

    public function check(){
        $input = $this->request->post();
        $result = ValidateHelper::execValidate('Admin', 'login', $input);
        if($result !== true){
            return $this->error($result);
        }

        $username = $input['username'];
        if(Check::isEmail($username)){
            $where = "email = '$username' and email != ''";
        }
        else{
            $where = "ad_account = '$username'";
        }
        $admin = AdminModel::where($where)->find();

        if(!$admin){
            return $this->error("账号不存在");
        }
        $pwd = AdminModel::makePassword($input['password'], $admin['salt']);
        if($pwd !== $admin['password']){
            return $this->error("密码错误");
        }
        if($admin['status'] !== 1){
            return $this->error("该账号已被禁用");
        }

        $ad_id = $admin['ad_id'];
        session('ad_id', $ad_id);
        AdminLogsModel::log($ad_id, 1);
        $this->success("登录成功", url("/admin/index"));
    }

    public function logout(){
        if(empty(session('ad_id'))){
            $this->success('用户未进行登录操作，无法登出');
        }
        else{
            session('ad_id', null);
            session('menu_list', null);
            $this->success('登出成功');
        }
    }
}
