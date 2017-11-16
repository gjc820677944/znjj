<?php
namespace app\admin\controller;

use app\common\model\admin\AdminAuthRuleModel;
use app\common\model\admin\AdminModel;
use app\common\validate\ValidateHelper;
use helper\AdminAuth;
use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $request;
    protected $ad_id;

    protected function _initialize()
    {
        parent::_initialize();
        $ad_id = AdminModel::getLogignAdId();
        if(empty($ad_id)){
            return $this->redirect(url("login/index"));
        }
        $this->ad_id = $ad_id;
        $log_admin = AdminModel::getLoginUser($ad_id);
        $this->assign("log_admin", $log_admin);
        
        $this->request = Request::instance();
        $controller_name = $this->request->controller();
        $action_name = $this->request->action();
        $http_referer = $this->request->server('HTTP_REFERER');
        $this->assign("controller_name", $controller_name);
        $this->assign("action_name", $action_name);
        $this->assign("referer_url",  $http_referer);

        //管理员菜单
        if(empty(session('menu_list'))){
            $menu_list = AdminModel::getMenuList($this->ad_id);
            session('menu_list', $menu_list);
        }
        $this->assign("menu_list",  session('menu_list'));

        //校验用户权限
        $auth = new AdminAuth();
        $curr_rule = AdminAuthRuleModel::getCurrRule();
        if($this->ad_id !== 1 && !$auth->check($curr_rule, $this->ad_id)){
            if($this->request->isAjax()){
                api_return_json(21, '权限不足，无法访问');
            }
            else{
                $this->error("权限不足，无法访问");
            }
        }
        //管理员当前路由ID
        $rule_level_ids = AdminAuthRuleModel::getLevelIds();
        $this->assign("rule_level_ids",  $rule_level_ids);
    }

    /**
     * 执行通用验证器操作
     * @param string $class_name 验证器类名
     * @param string $scene 验证器场景
     * @param array $input 输入数据
     * @return true|string
     */
    protected function execValidate($class_name, $scene, $input){
        $result = ValidateHelper::execValidate($class_name, $scene, $input);
        if($result !== true){
            $this->error($result);
        }
    }
}
