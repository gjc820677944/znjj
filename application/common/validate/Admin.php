<?php
namespace app\common\validate;

use app\common\model\admin\AdminModel;
use helper\Check;
use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'ad_id' => 'require|gt:0',
        'ad_name' => 'require|isUnique',
        'username' => 'require',
        'password' => 'checkPassword',
        'mobile' => 'isMobile',
        'email' => 'isEmail',
    ];
    
    protected $message = [
        'ad_id' => '管理员ID必须大于0',
        'ad_name' => '管理员账号不能为空',
        'ad_name.isUnique' => '账号已被占用',
        'username' => '管理员账号不能为空',
        'password' => '密码长度不能小于6位',
        'mobile' => '手机号格式错误',
        'email' => '邮箱格式错误',
    ];
    
    protected $scene = [
        'login' => ['username','password'],
        'create' => ['ad_name', 'password', 'mobile', 'email'],
        'edit' => ['ad_id', 'ad_name', 'password', 'mobile', 'email'],
        'personal'=> ['ad_id', 'password', 'mobile', 'email'],
    ];

    protected function isMobile($value){
        if($value === ''){
            return true;
        }
        return Check::isMobile($value);
    }

    protected function isEmail($value){
        if($value === ''){
            return true;
        }
        return Check::isEmail($value);
    }

    protected function checkPassword($value, $rule, $input){
        if(isset($input['ad_id']) && (int)$input['ad_id'] > 0){ //更新操作
            if($value === ''){
                return true;
            }
        }
        return strlen($value) >= 6 ? true : false;
    }

    protected function isUnique($value, $rule, $input){
        $where = "ad_name = '$value'";
        if(isset($input['ad_id']) && (int)$input['ad_id'] > 0){ //更新操作
            $ad_id = $input['ad_id'];
            $where .= " and ad_id != $ad_id";
        }
        $count = AdminModel::where($where)->count();
        return $count ? false : true;
    }
}