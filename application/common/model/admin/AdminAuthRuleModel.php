<?php

namespace app\common\model\admin;

use think\Model;
use think\Request;

class AdminAuthRuleModel extends Model
{
    protected $name = "admin_auth_rule";

    /**
     * 获取当前路由规则
     */
    public static function getCurrRule(){
        $request = Request::instance();
        $url = $request->controller().'/'.$request->action();
        $rule_name = strtolower(substr($url, 0, 1)).substr($url, 1);
        return $rule_name;
    }

    /**
     * 获取规则表的3级ID
     */
    public static function getLevelIds(){
        $level_ids = [
            'one'=>0, 'two'=>0
        ];
        $curr_rule = self::getCurrRule();
        $rule = AdminAuthRuleModel::where("rule_name", $curr_rule)
            ->field("rule_id, parent_id")->find();
        if(empty($rule)){
            return $level_ids;
        }
        if($rule['parent_id'] > 0){
            $level_ids['one'] = $rule['parent_id'];
            $level_ids['two'] = $rule['rule_id'];
        }
        else{
            $level_ids['one'] = $rule['rule_id'];
            $level_ids['two'] = 0;
        }
        return $level_ids;
    }
}
