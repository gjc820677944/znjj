<?php

namespace app\common\model\device;

use think\Model;

class DevicePointCategoryModel extends Model
{
    protected $name = "device_point_category";
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    /**
     * 获取功能点分类列表
     */
    public static function lists(){
        $list = DevicePointCategoryModel::field("cate_id, cate_name")
            ->order("sort_by desc, cate_id asc")->select();
        return $list;
    }
}
