<?php
namespace app\common\validate;

use app\common\model\admin\AdminModel;
use helper\Check;
use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'ad_id' => 'require|gt:0',
        'ad_account' => 'require|isUnique',
        'password' => 'checkPwd',
        'email' => 'isEmail',
    ];
    
    protected $message = [
        'ad_id' => '管理员ID必须大于0',
        'ad_account' => '管理员账号不能为空',
        'ad_account.isUnique' => '管理员账号账号已存在',
        'password' => '密码长度不能小于6位',
        'email' => '邮箱格式错误',
    ];
    
    protected $scene = [
        'login' => ['ad_account','password'],
        'create' => ['ad_account', 'password'=>'require|checkPwd', 'email'],
        'edit' => ['ad_id', 'ad_account', 'password', 'email'],
        'personal'=> ['password', 'email'],
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

    protected function checkPwd($value, $rule, $input){
        if(isset($input['ad_id']) && (int)$input['ad_id'] > 0){ //更新操作
            if($value === ''){
                return true;
            }
        }
        return strlen($value) >= 6 ? true : false;
    }

    protected function isUnique($value, $rule, $input){
        $where = "ad_account = '$value'";
        if(isset($input['ad_id']) && (int)$input['ad_id'] > 0){ //更新操作
            $ad_id = $input['ad_id'];
            $where .= " and ad_id != $ad_id";
        }
        $count = AdminModel::where($where)->count();
        return $count ? false : true;
    }
}