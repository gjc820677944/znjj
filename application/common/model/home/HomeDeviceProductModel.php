<?php
namespace app\common\model\home;

use app\common\model\device\DeviceModelModel;
use think\Model;

class HomeDeviceProductModel extends Model
{
    protected $name = "home_device_product";
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    public function getProtocolNameAttr($value, $data){
        $protocol = $data['protocol'];
        return DeviceModelModel::$protocol_texts[$protocol];
    }

    /**
     * 获取指定家庭的标签
     * @param int $home_id
     */
    public static function getTags($home_id){
        $tags = HomeDeviceProductModel::where("home_id", $home_id)
            ->column("distinct tag");
        foreach ($tags as $k=>$v){
            if($v === '') unset($tags[$k]);
        }
        $tags = array_values($tags);
        return $tags;
    }
}
