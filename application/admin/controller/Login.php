<?php

namespace app\admin\controller;

use app\common\validate\ValidateHelper;
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
            $where = "ad_name = '$username'";
        }
        $admin = AdminModel::where($where)->find();
        if(!$admin){
            api_return_json(12, '用户名错误');
        }
        $pwd = AdminModel::makePassword($input['password'], $admin['salt']);
        if($pwd !== $admin['password']){
            api_return_json(12, '密码错误');
        }
        if($admin['status'] !== 1){
            api_return_json(12, '账号已被禁用');
        }
        $ad_id = $admin['ad_id'];
        session('admin_info', $admin);
        AdminModel::updateLoginLogs($ad_id);
        api_return_json(0, '登录成功');
    }

    public function logout(){
        session('admin_info', null);
        $this->success('退出成功', url('index'), [], 1);
    }
}
