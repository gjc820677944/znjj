<?php

namespace app\common\model\admin;

use think\Model;
use think\Request;

class AdminLogsModel extends Model
{
    protected $name = "admin_logs";

    public static $type_texts = [
        1 => '登录',
        2 => '管理员操作',
    ];
    public static $detail_texts = [
        1 => '登录网站控制台',
        2 => '',
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

    /**
     * 根据传递数据合并logdata中的数据
     * @param array|object $log
     * @return array
     */
    public static function mergeLogData($log){
        if(!is_array($log)){
            $log = $log->toArray();
        }
        $log_data = json_decode($log['log_data'], true);
        if(is_null($log_data)){
            $log_data = [];
        }
        return array_merge($log, $log_data);
    }

    /**
     * 根据传递的log对象，生成管理员操作日志详情
     * @param array|object $log
     * @return
     */
    public static function logDetail($log){
        $log = self::mergeLogData($log);
        $log_type = $log['log_type'];
        $template_str = self::$detail_texts[$log_type];
        if($log_type === 2){
            $operation_str = AdminOperationLogsModel::logDetail($log);
            if($operation_str === ''){
                return '';
            }
            $template_str .= $operation_str;
        }
        $log_detail = self::makeDetail($template_str, $log);
        return $log_detail;
    }

    /**
     * 根据模板字符串和传递数据生成相应字符串
     * @param $template_str
     * @param $data
     */
    public static function makeDetail($template_str, $data){
        preg_match_all('/{%([a-zA-Z0-9_]+)%}/', $template_str, $matchs);
        $keys = $matchs[1];
        if(empty($keys)){
            return $template_str;
        }
        foreach ($keys as $key) {
            if(isset($data[$key])){
                $val = $data[$key];
            }
            else{
                $val = '';
            }
            $template_str = str_replace('{%'.$key.'%}', $val, $template_str);
        }
        return $template_str;
    }
}
