<?php
namespace app\admin\controller;

use app\common\model\admin\AdminAuthGroupAccessModel;
use app\common\model\admin\AdminAuthRuleModel;
use app\common\model\admin\AdminModel;
use app\common\validate\ValidateHelper;
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

    protected function getMenulist(){
        $ad_id = $this->ad_id;
        $model = new AdminAuthRuleModel();
        if($ad_id !== 1){
            $role_ids = AdminModel::getRoleIds($ad_id);
            //$rules = AdminAuthGroupAccessModel::
        }
    }
}
