<?php
namespace app\common\model\home;

use think\Model;

class HomeDeviceProductModel extends Model
{
    protected $name = "home_device_product";
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    /**
     * 获取指定家庭的标签
     * @param int $home_id
     */
    public static function getTags($home_id){
        $tags = HomeDeviceProductModel::where("home_id", $home_id)
            ->column("distinct tag");
        return $tags;
    }
}
