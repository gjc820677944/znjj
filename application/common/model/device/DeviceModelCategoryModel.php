<?php

namespace app\common\model\device;

use think\Model;

class DeviceModelCategoryModel extends Model
{
    protected $name = "device_model_category";
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    /**
     * 获取模型分类列表
     */
    public static function lists(){
        $list = DeviceModelCategoryModel::field("cate_id, cate_name")
            ->order("sort_by desc, cate_id asc")->select();
        return $list;
    }
}
