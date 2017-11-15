<?php
namespace app\admin\controller\auth;

use app\admin\controller\Base;
use app\common\model\admin\AdminAuthRuleModel;

class Rule extends Base
{

    //规则
    function index(){
        $input = $this->request->param();
        $model = new AdminAuthRuleModel();
        if (isset($input['keywords']) && $input['keywords'] !== '') {
            $keywords = $input['keywords'];
            $model->where("aa.rule_title like '%$keywords%'");
        }

        $list = $model->alias('aa')->field('aa.rule_id,aa.rule_name,aa.rule_title,aa.status,aa.sort_by,aa.parent_id,aa.show_menu')->select();
        $list = collection($list)->toArray();
        $list = list_to_levellist($list, 'rule_id', 'parent_id');
        $data = [
            'list' => $list,
            'input' => $input,
            'create_url' => url('create'),
        ];

        return $this->fetch('', $data);

    }

    function create()
    {
        $input = $this->request->param();

        //1是添加规则
        if ($input['type'] == 1) {
            $rule_info = [
                'rule_id' => 0,
                'rule_name' => '',
                'rule_title' => '',
                'status' => '',
                'sort_by' => 1,
                'parent_id' => '',
                'show_menu'     =>  -1,
                'icon'      =>'',
            ];
        } else {
            $rule_info = AdminAuthRuleModel::where('rule_id=' . $input['rule_id'])->find();
        }
        if(empty($input['rule_id'])){
            $input['rule_id'] = 0;
        }
        $rule = AdminAuthRuleModel::where('rule_id !=' . $input['rule_id'])->select();
        $rule = $this->digui($rule);
        $data = [
            'data' => $rule_info,
            'rule' =>$rule,
            'post_url' => url('save'),
            'type' => $input['type'],
        ];
        return $this->fetch('edit', $data);
    }

    //添加权限或修改
    public function ruleInfo()
    {
        $input = $this->request->param();
        //如果user_id等于0就是添加 反之修改
        if ($input['rule_id'] == 0) {
            Rule::addRule($input);
        } else {
            Rule::updateRule($input);
        }

    }

//添加权限
    function addRule($input)
    {
        $referer_url=$input['referer_url'];
        unset($input['rule_id']);
        unset($input['referer_url']);
        $info=AdminAuthRuleModel::insert($input);
        if ($info){
            $this->success("添加成功",$referer_url);
        }else{
            $this->error("手机号已存在");
        }

    }

    //修改权限
    function updateRule($input)
    {
        $rule_id=$input['rule_id'];
        $referer_url=$input['referer_url'];
        unset($input['rule_id']);
        unset($input['referer_url']);
        $info=AdminAuthRuleModel::where('rule_id='.$rule_id)->update($input);
        if ($info){
            $this->success("修改成功",$referer_url);
        }else{
            $this->error("修改失败");
        }
    }

    //删除规则
    function deleteRuleInfo(){
        $input = $this->request->param();
        $user_id=$input['rule_id'];
        AdminAuthRuleModel::where('')->delete();


    }

    //递归获取权限等级
    function digui($data,$j=0,$lev=0)
    {
        $subs=array();//存放子孙数组
        foreach ($data as $v){
            if ($v['parent_id']==$j){
                $v['length']=str_repeat('----',$lev);
                $subs[]=$v;
                $subs=array_merge($subs,$this->digui($data,$v['rule_id'],$lev+1 ));
            }

        }
        return $subs;
    }

}





