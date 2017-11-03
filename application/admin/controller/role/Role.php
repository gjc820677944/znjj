<?php
namespace app\admin\controller\role;
use app\admin\controller\Base;
use app\common\model\admin\AdminAuthRuleModel;
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

    //添加或修改角色
    function create()
    {
        $input = $this->request->param();

        //1是添加规则
        if ($input['type'] == 1) {
            $rule_info = [
                'group_id' => 0,
                'title' => '',
                'status' => '',
            ];
        } else {
            $rule_info = AdminRoleModel::where('group_id=' . $input['group_id'])->find();
        }
        $rule= $data=AdminAuthRuleModel::select();
        $arr=$this->digui($rule);
        $data = [
            'data' => $rule_info,
            'post_url' => url('save'),
            'type' => $input['type'],
        ];
        return $this->fetch('edit', $data);
    }


    //递归获取权限等级
    function digui($data,$j=0)
    {
        $subs=array();//存放子孙数组
        foreach ($data as $v){
            if ($v['parent_id']==$j){
                $v['zi']=$this->digui($data,$v['rule_id'] );
                $subs[]=$v;
            }
        }
        return $subs;
    }


}





