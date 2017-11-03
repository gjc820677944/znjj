<?php

namespace app\admin\controller\config;


use app\admin\controller\Base;
use app\common\model\admin\AdminOperationLogsModel;
use app\common\model\config\WebConfigModel;
use app\common\validate\ValidateHelper;

class Webconfig extends Base
{
    public function index()
    {
        $list = WebConfigModel::order("sort_by desc, conf_id asc")->select();
        $data = [
            'list' => $list,
            'create_url' => url('create'),
            'post_url' => url('updateList'),
        ];
        return $this->fetch('', $data);
    }

    public function create()
    {
        $data = [
            'conf_id' => 0,
            'conf_name' => '',
            'conf_key' => '',
            'conf_value' => '',
            'summary' => '',
            'sort_by' => 0,
            'type' => '',
        ];
        $data = [
            'data' => $data,
            'type_texts' => WebConfigModel::$typeTexts,
            'post_url' => url('save'),
        ];
        return $this->fetch('edit', $data);
    }

    public function save()
    {
        $input = $this->request->except(['conf_id'], 'post');
        $this->execValidate('WebConfig', 'create', $input);
        $result = WebConfigModel::create($input);
        if($result){
            WebConfigModel::makeConfigFile();
            AdminOperationLogsModel::log("添加网站全局配置项[id:".$result->conf_id."]");
            $this->success("创建成功", url("index"));
        }
        else{
            $this->error("创建失败，请重新尝试");
        }
    }

    public function edit($id)
    {
        $data = WebConfigModel::get($id);
        if(empty($data)){
            $this->error("无相关配置信息");
        }
        $data = [
            'data' => $data,
            'type_texts' => WebConfigModel::$typeTexts,
            'post_url' => url('update'),
        ];
        return $this->fetch('edit', $data);
    }

    public function update()
    {
        $input = $this->request->post();
        $this->execValidate('WebConfig', 'edit', $input);
        $result = WebConfigModel::update($input);
        if($result){
            WebConfigModel::makeConfigFile();
            AdminOperationLogsModel::log("更新网站全局配置项[id:".$result->conf_id."]");
            $this->success("更新成功", url("index"));
        }
        else{
            $this->error("更新失败，请重新尝试");
        }
    }

    public function delete()
    {
        $id = (int)$this->request->param('id');
        $result = WebConfigModel::destroy($id);
        if($result){
            WebConfigModel::makeConfigFile();
            AdminOperationLogsModel::log("删除网站全局配置项[id:".$id."]");
            api_return_json(0, "删除成功");
        }
        else{
            api_return_json(0, "删除失败，请重新尝试");
        }
    }

    public function updateList(){
        $input = $this->request->post();
        if(empty($input)){
            $this->success("未提交配置参数，无法修改");
        }
        foreach ($input as $k=>$v){
            WebConfigModel::where("conf_key = '$k'")->update(['conf_value'=>$v]);
        }
        WebConfigModel::makeConfigFile();
        AdminOperationLogsModel::log("更新网站全局配置");
        $this->success("更新成功");
    }
}
