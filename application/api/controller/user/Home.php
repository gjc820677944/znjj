<?php

namespace app\api\controller\user;

use app\common\model\device\DeviceModelModel;
use app\common\model\home\HomeDeviceProductModel;
use app\common\model\home\HomeLeaguerModel;
use app\common\model\home\HomeModel;
use filehelper\FileHelper;
use think\Request;

class Home extends Base
{
    //创建家庭
    public function create(){
        $input = $this->requset->param();
        if(!isset($input['home_name']) || $input['home_name'] === ''){
            api_return_json(301, "家庭名字不能为空");
        }
        $home_name = trim($input['home_name']);
        if(!empty($input['serial_number'])){
            $serial_number = trim($input['serial_number']);
        }
        else{
            $serial_number = '';
        }

        $where = "is_gateway = 1 and status = 1";
        $model_id = DeviceModelModel::getIdBySN($serial_number, $where);

        //添加房间与家庭
        $model = new HomeModel();
        $model->startTrans();
        //插入房间
        $home_data = [
            'creater_id' => $this->user_id,
            'home_name' => $home_name,
        ];
        $home = HomeModel::create($home_data);
        if(empty($home)){
            $model->rollback();
            api_return_json(304, "房间创建失败，请重新尝试");
        }

        if($model_id > 0){
            //插入网关设备
            $product_data = [
                'home_id' => $home->home_id,
                'serial_number' => $serial_number,
                'model_id' => $model_id,
                'is_gateway' => 1,
            ];
            $product = HomeDeviceProductModel::create($product_data);
            if(empty($product)){
                $model->rollback();
                api_return_json(305, "网关创建失败，请重新尝试");
            }
        }

        //插入家庭成员（创建人）
        $leaguer_data = [
            'home_id' => $home->home_id,
            'leaguer_id' => $this->user_id,
            'remark' => '',
            'auth' => HomeLeaguerModel::makeAuth(['Y', 'Y']),
        ];
        $leaguer = HomeLeaguerModel::create($leaguer_data);
        if(empty($leaguer)){
            $model->rollback();
            api_return_json(306, "家庭创建人添加失败，请重新尝试");
        }
        $model->commit();
        api_return_json(0);
    }

    //获取家庭列表
    public function index(){
        $home_ids = HomeLeaguerModel::where("leaguer_id", $this->user_id)->column("home_id");
        $field = "h.home_id, h.home_name, h.creater_id, hu.username creater_name, ".
            "hu.avatar creater_avatar, h.wallpaper, h.create_time";
        $list = HomeModel::alias("h")->field($field)
            ->join("user hu", "hu.user_id = h.creater_id")
            ->where("h.home_id", "in", $home_ids)->select();
        foreach ($list as $k=>$v){
            $v['creater_avatar'] = FileHelper::helper()->getWebsitePath($v['creater_avatar']);
            $v['wallpaper'] = FileHelper::helper()->getWebsitePath($v['wallpaper']);
            if($v['creater_id'] === $this->user_id){ //是否为家庭创建人
                $v['is_creater'] = true;
            }
            else{
                $v['is_creater'] = false;
            }
            $v['tags'] = HomeDeviceProductModel::getTags($v['home_id']);
            $list[$k] = $v;
        }
        api_return_json(0, $list);
    }

    //获取家庭信息详情
    public function info(){
        $home_id = (int)$this->requset->param('home_id');
        if($home_id <= 0){
            api_return_json(311, '家庭ID错误');
        }
        $field = "h.home_id, h.home_name, h.creater_id, hu.username creater_name, ".
            "hu.avatar creater_avatar, h.wallpaper, h.create_time";
        $home = HomeModel::alias("h")->field($field)
            ->join("user hu", "hu.user_id = h.creater_id")
            ->where("h.home_id", $home_id)->find();
        if(empty($home)){
            api_return_json(312, "无法查询到相关家庭");
        }
        $home['creater_avatar'] = FileHelper::helper()->getWebsitePath($home['creater_avatar']);
        $home['wallpaper'] = FileHelper::helper()->getWebsitePath($home['wallpaper']);
        if($home['creater_id'] === $this->user_id){ //是否为家庭创建人
            $home['is_creater'] = true;
        }
        else{
            $home['is_creater'] = false;
        }
        $home['tags'] = HomeDeviceProductModel::getTags($home['home_id']);

        $field = "l.leaguer_id, lu.username leaguer_name, lu.avatar, l.remark, l.create_time";
        $leaguers = HomeLeaguerModel::alias("l")->field($field)
            ->join("user lu", "lu.user_id = l.leaguer_id", "left")
            ->where("home_id", $home_id)->order("create_time desc")->select();
        foreach ($leaguers as $k=>$v){
            $v['avatar'] = FileHelper::helper()->getWebsitePath($v['avatar']);
            $leaguers[$k] = $v;
        }
        $home['leaguers'] = $leaguers;
        api_return_json(0, $home);
    }

    //修改家庭信息
    public function edit(){
        $input = $this->requset->param();
        if(empty($input['home_id'])){
            api_return_json(321, "房间ID错误");
        }
        $home_id = (int)$input['home_id'];
        if(!HomeModel::checkCreater($home_id, $this->user_id)){
            api_return_json(333, "非家庭创建人，无权限修改家庭信息");
        }

        $data = [
            'home_id' => $home_id,
        ];
        if(!empty($input['home_name'])){ //家庭名字
            $data['home_name'] = trim($input['home_name']);
        }
        if($file = Request::instance()->file('wallpaper')){ //家庭封面
            $data['wallpaper'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'home/wallpaper/'.date("Ymd"));
        }

        $result = HomeModel::update($data);
        if($result){
            api_return_json(0);
        }
        else{
            api_return_json(324, "更新失败，请重新尝试");
        }
    }

    //移交家庭创建人
    public function handOver(){
        $home_id = (int)$this->requset->param('home_id');
        $leaguer_id = (int)$this->requset->param('leaguer_id');
        if(empty($home_id)){
            api_return_json(331, "家庭ID错误");
        }
        if(empty($leaguer_id)){
            api_return_json(332, "新创建人ID错误");
        }

        if(!HomeModel::checkCreater($home_id, $this->user_id)){
            api_return_json(333, "非家庭创建人，无权限修改家庭信息");
        }
        if($leaguer_id === $this->user_id){
            api_return_json(334, "新创建人ID不能和旧创建人相同");
        }

        $home_leaguer_ids = HomeLeaguerModel::where("home_id", $home_id)->column('leaguer_id');
        if(!in_array($leaguer_id, $home_leaguer_ids)){
            api_return_json(335, "新家庭创建人不是家庭成员");
        }
        $model = new HomeModel();
        $model->startTrans();
        $result = HomeModel::update(['home_id'=>$home_id, 'creater_id'=>$leaguer_id]);
        //修改权限
        HomeLeaguerModel::update([
            'home_id'=>$home_id,
            'leaguer_id'=>$leaguer_id,
            'auth'=>HomeLeaguerModel::makeAuth(['Y', 'Y'])
        ]);
        HomeLeaguerModel::update([
            'home_id'=>$home_id,
            'leaguer_id'=>$this->user_id,
            'auth'=>HomeLeaguerModel::makeAuth(['N', 'N'])
        ]);
        //修改权限
        if($result){
            $model->commit();
            api_return_json(0);
        }
        else{
            $model->rollback();
            api_return_json(324, "更新失败，请重新尝试");
        }
    }

    //解散家庭
    public function delete(){
        $home_id = (int)$this->requset->param('home_id');
        if(empty($home_id)){
            api_return_json(331, "家庭ID错误");
        }
        $home = HomeModel::field("creater_id")->find($home_id);
        if(empty($home)){
            api_return_json(332, "无法查询到相关家庭");
        }
        if($home['creater_id'] !== $this->user_id){
            api_return_json(333, "非家庭创建人，无权解散家庭");
        }
        $result = HomeModel::remove($home_id);
        if($result){
            api_return_json(0);
        }
        else{
            api_return_json(334, "解散失败，请重新尝试");
        }
    }
}