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
        1 => '管理员{%ad_account%}[id:{%ad_id%}]登录了控制台',
        2 => '管理员{%ad_account%}[id:{%ad_id%}]{%operation%}',
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
        $log_detail = self::makeDetail($template_str, $log);
        return $log_detail;
    }

    /**
     * 根据模板字符串和传递数据生成相应字符串
     * @param $template_str
     * @param $data
     */
    protected static function makeDetail($template_str, $data){
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

    /**
     * 记录管理员的后台操作
     * @param string $controller 操作控制器
     * @param string $action 操作 [create=>添加，update=>更新 delete=》删除]
     * @param mixed $id 更新的数据ID或ID集合
     * @return bool
     */
    public static function logOperation($controller, $action, $id = null){
        $ad_id = AdminModel::getLogignAdId();
        $log_type = 2;
        $ad_account = AdminModel::where("ad_id = $ad_id")->value("ad_account");
        $request = Request::instance();
        if(is_array($id)){
            $id = implode(',', $id);
        }
        $data = [
            'ad_id' => $ad_id,
            'ad_account' => $ad_account,
            'log_type' => $log_type,
            'log_ip' => $request->ip(),
            'log_time' => time(),
            'log_data' => ['controller'=>$controller, 'action'=>$action, 'id'=>$id],
        ];
        $result = AdminLogsModel::create($data);
        return $result ? true : false;
    }

    /**
     * 获取管理员操作详情
     */
    public static function logOperationDetail($log){
        $texts = [
            'Admin' => '{%action%}了一个管理员[id:{%ad_id%}]',
            'Edition' => '{%action%}了一条APP版本记录[id:{%ad_id%}]',
            'User' => '{%action%}了一个用户[id:{%user_id%}]',
            'Home' => '{%action%}了用户家庭[id:{%home_id%}]',
            'HomeDeviceProduct' => '%s了家庭设备',
            'HomeLeaguer' => '%s了家庭成员',
            'DeviceModel' => '%s了一个智能设备模型',
            'DeviceModelPoint' => '%s了一个智能设备模型',
            'DeviceModelCategory' => '%s了一个智能设备模型分类',
            'DevicePoint' => '%s了一个设备功能点',
            'DevicePointCategory' => '%s了一个设备功能点分类',
            'Webconfig' => '%s了网站全局配置项',
        ];
    }
}
