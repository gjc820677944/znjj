<?php

namespace app\common\model\admin;

use think\Model;
use think\Request;

class AdminOperationLogsModel extends Model
{
    protected $name = "admin_logs";
    protected static $actionTexts = [
        'create' => '添加',
        'update' => '更新',
        'delete' => '删除',
    ];
    protected static $operationTexts = [
        'Admin' => [
            'default' => '{%action%}了管理员[id: {%ad_id%}]',
        ],
        'User' => [
            'default' => '{%action%}了用户[id: {%user_id%}]',
        ],
        'Edition' => [
            'default' => '{%action%}了APP版本记录[id: {%edtion_id%}]',
        ],
        'Home' => [
            'default' => '{%action%}了用户家庭[id: {%home_id%}]',
        ],
        'HomeLeaguer' => [
            'default' => '{%action%}了家庭成员[Home id: {%home_id%}, Leaguerid: {%leaguer_id%}]',
        ],
        'HomeDeviceProduct' => [
            'default' => '{%action%}了用户家庭设备[id: {%product_id%}]',
        ],
        'DeviceModel' => [
            'default' => '{%action%}了智能设备模型[id: {%model_id%}]',
        ],
        'DeviceModelCategory' => [
            'default' => '{%action%}了智能设备模型分类[id: {%cate_id%}]',
        ],
        'DeviceModelPoint' => [
            'default' => '对智能设备{%action%}[id: {%model_id%}]了功能点[id: {%point_id%}]',
        ],
        'DevicePoint' => [
            'default' => '{%action%}了智能设备功能点[id: {%point_id%}]',
        ],
        'DevicePointCategory' => [
            'default' => '{%action%}了智能设备功能点分类[id: {%cate_id%}]',
        ],

        'Webconfig' => [
            'default' => '{%action%}一项网站全局配置[id: {%cate_id%}]',
            'updateList' => '更新了网站全局配置',
        ],
    ];

    /**
     * 记录管理员的后台操作
     * @param string $action 操作 [create=>添加，update=>更新 delete=》删除]
     * @param mixed $params 参数集合
     * @return bool
     */
    public static function log($action, $params = null){
        $ad_id = AdminModel::getLogignAdId();
        $log_type = 2;
        $ad_account = AdminModel::where("ad_id = $ad_id")->value("ad_account");
        $request = Request::instance();
        $controller = $request->controller();
        if(count(explode('.', $controller)) >= 2){
            $array = explode('.', $controller);
            $controller = ucfirst($array[1]);
        }
        $data = [
            'ad_id' => $ad_id,
            'ad_account' => $ad_account,
            'log_type' => $log_type,
            'log_ip' => $request->ip(),
            'log_time' => time(),
            'log_data' => json_encode([
                'controller'=>$controller,
                'action'=>$action,
                'params'=>$params,
            ]),
        ];
        $result = AdminLogsModel::create($data);
        return $result ? true : false;
    }

    /**
     * 获取管理员操作详情
     */
    public static function logDetail($log){
        $log_type = $log['log_type'];
        if($log_type !== 2){
            return '';
        }

        $log_data = json_decode($log['log_data'], true);
        if(empty($log_data)){
            return '';
        }
        $controller = $log_data['controller'];
        $action = $log_data['action'];
        $params = $log_data['params'];
        $action_texts = self::$actionTexts;
        $operation_texts = self::$operationTexts;
        if(!isset($operation_texts[$controller])){
            return '';
        }
        if(!is_array($params)){
            $params = [];
        }
        if(isset($action_texts[$action])){
            $params['action'] = $action_texts[$action];
        }
        $texts = $operation_texts[$controller];
        $template_str = (isset($texts[$action]) ? $texts[$action] : $texts['default']);
        return AdminLogsModel::makeDetail($template_str, $params);
    }
}
