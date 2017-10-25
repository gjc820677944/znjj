<?php

namespace app\common\model\admin;

use think\Model;
use think\Request;

class AdminLogsModel extends Model
{
    protected $name = "admin_logs";

    public static $type_texts = [
        1 => '登录',
    ];

    public static function log($ad_id, $log_type, $log_data = null){
        $ad_account = AdminModel::where("ad_id = $ad_id")->value("ad_account");
        $request = Request::instance();
        $data = [
            'ad_id' => $ad_id,
            'ad_account' => $ad_account,
            'log_type' => $log_type,
            'log_ip' => $request->ip(),
            'log_time' => time(),
            'log_data' => ($log_data ? json_encode($log_data) : ''),
        ];
        $result = AdminLogsModel::create($data);
        return $result ? true : false;
    }
}
