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
            $gz=array();
        } else {
            $rule_info = AdminRoleModel::where('group_id=' . $input['group_id'])->find();
            $gz=explode(',',$rule_info['rule_ids']);
        }
        $rule= $data=AdminAuthRuleModel::select();
        $arr=$this->digui($rule);
//        var_dump($gz);exit;
        $data = [
            'data' => $rule_info,
            'rule'  =>  $arr,
            'post_url' => url('save'),
            'type' => $input['type'],
            'gz'    =>  $gz,
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

    //角色信息
    function roleInfo()
    {
        $input = $this->request->param();

        if (empty($input['Fruit'])){
            $this->error("请给角色分配权限");
        }
        if ($input['group_id']>0){
            $this->updateRole($input);
        }else{
            $this->addRole($input);
        }

    }
    //角色修改
    function updateRole($input)
    {
        $rule=implode(',',$input['Fruit']);
        $data['group_id']=$input['group_id'];
        $data['title']=$input['title'];
        $data['status']=$input['status'];
        $data['rule_ids']=$rule;
        $info=AdminRoleModel::update($data);
        if ($info===false){
            $this->error("修改失败");
        }else{
            $this->success("修改成功",$input['referer_url']);
        }
    }

    //角色添加
    function addRole($input)
    {
        $rule=implode(',',$input['Fruit']);
        $data['title']=$input['title'];
        $data['status']=$input['status'];
        $data['rule_ids']=$rule;
        $info=AdminRoleModel::create($data);
        if ($info!=false){
            $this->success("添加成功",$input['referer_url']);
        }else{
            $this->error("添加失败");
        }
    }

    //删除角色
    function deleteRoleInfo(){
        $input = $this->request->param();
        $info=AdminRoleModel::where('group_id='.$input['group_id'])->delete();
        if ($info!==false){
            api_return_json(0, "删除成功");
        }else{
            api_return_json(1, "删除失败");
        }

    }


}





