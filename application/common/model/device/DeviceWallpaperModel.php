<?php

namespace app\common\model\device;
use think\Model;
use filehelper\FileHelper;

class DeviceWallpaperModel extends Model
{
    protected $name = "device_wallpaper";

    public static function rmUrlByid($id){
        $vo = DeviceWallpaperModel::field("url")->find($id);
        if($vo && $vo['url']){
            FileHelper::helper()->unlink($vo['url']);
        }
    }
}
