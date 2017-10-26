<?php
namespace app\admin\controller;

use app\common\model\admin\AdminModel;
use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $request;

    protected function _initialize()
    {
        parent::_initialize();
        $ad_id = session("ad_id");
        if(empty($ad_id)){
            return $this->redirect(url("login/index"));
        }
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
}
