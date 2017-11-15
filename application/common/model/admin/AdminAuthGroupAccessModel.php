<?php

namespace app\common\model\admin;

use think\Model;

class AdminAuthGroupAccessModel extends Model
{
    protected $name = "admin_auth_group_access";

    public static function saveGroupIds($ad_id, $group_ids){
        if(!is_array($group_ids)){
            $group_ids = explode(',', $group_ids);
        }
        $list = [];
        foreach ($group_ids as $group_id){
            $group_id = (int)$group_id;
            if($group_id > 0){
                $list[] = [
                    'ad_id' => $ad_id,
                    'group_id' => $group_id,
                ];
            }
        }
        if (empty($list)){
            return false;
        }
        AdminAuthGroupAccessModel::where("ad_id", $ad_id)->delete(); //删除旧数据
        (new AdminAuthGroupAccessModel)->saveAll($list);
        return true;
    }
}
