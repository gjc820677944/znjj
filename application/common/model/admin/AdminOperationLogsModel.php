<?php

namespace app\common\model\admin;

use think\Model;
use think\Request;

class AdminOperationLogsModel extends Model
{
    protected $name = "admin_logs";
    protected static $actionTexts = [
        'save' => '添加',
        'update' => '更新',
        'delete' => '删除',
    ];
    protected static $operationTexts = [
        'Admin.admin' => [
            'default' => '{%action%}管理员[id: {%ad_id%}]',
        ],
        'User.user' => [
            'default' => '{%action%}用户[id: {%user_id%}]',
        ],
        'Edition.edition' => [
            'default' => '{%action%}APP版本记录[id: {%edtion_id%}]',
        ],
        'Home.home' => [
            'default' => '{%action%}用户家庭[id: {%home_id%}]',
        ],
        'Home.homeDeviceProduct' => [
            'default' => '{%action%}用户家庭设备[id: {%product_id%}]',
        ],
        'Device.deviceModel' => [
            'default' => '{%action%}智能设备模型[id: {%model_id%}]',
        ],
        'Device.deviceModelCategory' => [
            'default' => '{%action%}智能设备模型分类[id: {%cate_id%}]',
        ],
        'Device.deviceModelPoint' => [
            'default' => '对智能设备[id: {%model_id%}]{%action%}了功能点',
        ],
        'Device.devicePoint' => [
            'default' => '{%action%}智能设备功能点[id: {%point_id%}]',
        ],
        'Device.devicePointCategory' => [
            'default' => '{%action%}智能设备功能点分类[id: {%cate_id%}]',
        ],

        'Config.webconfig' => [
            'default' => '{%action%}一个全局配置项[id: {%cate_id%}]',
            'updateList' => '更新了网站全局配置',
        ],
    ];

    /**
     * 记录管理员的后台操作
     * @param mixed $params 参数集合
     * @return bool
     */
    public static function log($params = null, $action = null){
        $ad_id = AdminModel::getLogignAdId();
        $log_type = 2;
        $ad_account = AdminModel::where("ad_id = $ad_id")->value("ad_account");
        $request = Request::instance();
        $controller = $request->controller();
        if($action === null){
            $action = $request->action();
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
