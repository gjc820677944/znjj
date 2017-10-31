<?php
namespace app\common\model\home;

use think\Model;

class HomeLeaguerModel extends Model
{
    protected $name = "home_leaguer";
    protected $autoWriteTimestamp = true;
    protected $updateTime = false;

    /**
     * 根据传递的数组生成相应的权限
     * @param array $auths
     */
    public static function makeAuth($auths = []){
        return implode(',', $auths);
    }
}
