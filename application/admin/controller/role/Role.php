<?php
namespace app\admin\controller\role;
use app\admin\controller\Base;
use app\common\model\admin\AdminRoleModel;
use think\Request;
use filehelper\FileHelper;
use think\Db;

class Role extends Base
{

    //角色
    function index(){
        $input = $this->request->param();
        $model = new AdminRoleModel();
        if (isset($input['keywords']) && $input['keywords'] !== '') {
            $keywords = $input['keywords'];
            $model->where("title like '%$keywords%'");
        }
//        if (isset($input['status']) && $input['status'] !== '') {
//            $status = (int)$input['status'];
//            $model->where("status", $status);
//        }
        $list = $model->paginate(null);
        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'create_url' => url('create'),
        ];

        return $this->fetch('', $data);

    }

}





