<?php

namespace app\common\model\admin;

use think\Model;
use think\Request;

class AdminOperationLogsModel extends Model
{
    protected $name = "admin_logs";

    /**
     * 记录管理员的后台操作
     * @param string $message 消息内容
     * @param mixed $params 参数集合
     * @return bool
     */
    public static function log($message, $data = null){
        $ad_id = AdminModel::getLogignAdId();
        $request = Request::instance();
        $log_data = [
            'message'=>$message,
            'controller'=>$request->controller(),
            'action'=>$request->action(),
            'data'=>$data,
        ];
        $result = AdminLogsModel::log($ad_id, 2, $log_data);
        return $result ? true : false;
    }

    /**
     * 获取管理员操作详情
     */
    public static function logDetail($log){
        $log_data = json_decode($log['log_data'], true);
        if(empty($log_data)){
            return '';
        }
        if(isset($log_data['message'])){
            return $log_data['message'];
        }
        else{
            return '';
        }
    }
}
