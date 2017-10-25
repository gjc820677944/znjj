<?php

namespace app\common\model\admin;

use filehelper\FileHelper;
use think\Model;

class AdminModel extends Model
{
    protected $name = "admin";
    protected $autoWriteTimestamp = true;
    protected $hidden = ['password', 'salt'];

    protected function getStatusTextAttr($val, $data){
        $status = $data['status'];
        $texts = [1=>'正常', 4=>'已禁用'];
        return $texts[$status];
    }

    protected static function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        self::event("after_update", function($model){
            if(isset($model->ad_id)){
                AdminModel::rmAvatarByid($model->ad_id);
            }
        });
        self::event("after_delete", function($model){
            if(isset($model->ad_id)){
                AdminModel::rmAvatarByid($model->ad_id);
            }
        });
    }

    /**
     * 生成管理员密码
     * @param string $password 密码明文
     * @param string $salt 加密盐
     * @return string
     */
    public static function makePassword($password, $salt){
        return md5(md5($password).$salt);
    }

    /**
     * 根据管理员ID删除用户头像
     * @param int $id 管理员ID
     */
    public static function rmAvatarByid($id){
        $vo = AdminModel::field("avatar")->find($id);
        if($vo && $vo['avatar']){
            FileHelper::helper()->unlink($vo['avatar']);
        }
    }
}
