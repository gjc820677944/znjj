<?php

namespace app\common\model\admin;

use think\Model;

class AdminModel extends Model
{
    protected $name = "admin";
    protected $autoWriteTimestamp = true;

    /**
     * 生成管理员密码
     * @param string $password 密码明文
     * @param string $salt 加密盐
     * @return string
     */
    public static function makePassword($password, $salt){
        return md5(md5($password).$salt);
    }
}
