<?php
namespace app\common\model\home;

use filehelper\FileHelper;
use think\Model;

class HomeModel extends Model
{
    protected $name = "home";
    protected $autoWriteTimestamp = true;

    protected static function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        self::event("before_update", function($model){
            if(isset($model->home_id) && isset($model->wallpaper)){
                HomeModel::rmWallpaperByid($model->home_id);
            }
        });
        self::event("before_delete", function($model){
            if(isset($model->home_id)){
                HomeModel::rmWallpaperByid($model->home_id);
            }
        });
    }

    /**
     * 根据ID删除壁纸
     * @param int $id 管理员ID
     */
    public static function rmWallpaperByid($id){
        $vo = HomeModel::field("wallpaper")->find($id);
        if($vo && $vo['wallpaper']){
            FileHelper::helper()->unlink($vo['wallpaper']);
        }
    }

    /**
     * 解散家庭
     * @param int $home_id
     * @return bool
     */
    public static function remove(int $home_id){
        $model = new HomeModel();
        $model->startTrans();
        $res1 = HomeModel::destroy($home_id);
        $res2 = HomeLeaguerModel::where("home_id", $home_id)->delete();
        $res3 = HomeLeaguerInviteModel::where("home_id", $home_id)->delete();
        $res4 = HomeDeviceProductModel::where("home_id", $home_id)->delete();
        if($res1 && $res2){
            $model->commit();
            return true;
        }
        else{
            $model->rollback();
            return false;
        }
    }

    /**
     * 校验用户是不是家庭创建人
     * @param int $home_id 家庭ID
     * @param int $user_id 用户ID
     */
    public static function checkCreater($home_id, $user_id){
        $creater_id = HomeModel::where("home_id", $home_id)->value("creater_id");
        if($creater_id === $user_id){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 创建家庭
     * @param int $creater_id 创建人ID
     * @param string $home_name 房间名称
     */
    public static function createHome($creater_id, $home_name){
        //添加房间与家庭
        $model = new HomeModel();
        $model->startTrans();
        //插入房间
        $home_data = [
            'creater_id' => $creater_id,
            'home_name' => $home_name,
        ];
        $home = HomeModel::create($home_data);
        if(empty($home)){
            $model->rollback();
            return false;
        }
        //插入家庭成员（创建人）
        $leaguer_data = [
            'home_id' => $home->home_id,
            'leaguer_id' => $creater_id,
            'remark' => '',
            'auth' => HomeLeaguerModel::makeAuth(['Y', 'Y']),
        ];
        $leaguer = HomeLeaguerModel::create($leaguer_data);
        if(empty($leaguer)){
            $model->rollback();
            return false;
        }
        $model->commit();
        return true;
    }

}