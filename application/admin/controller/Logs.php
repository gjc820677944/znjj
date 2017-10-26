<?php

namespace app\admin\controller;

use app\common\model\admin\AdminLogsModel;

class Logs extends Base
{
    public function index(){
        $input = $this->request->param();
        $model = new AdminLogsModel();
        if(isset($input['keywords']) && $input['keywords'] !== ''){
            $keywords = trim($input['keywords']);
            if(preg_match('/^[0-9]+$/', $keywords)){ //ID数字
                $model->where("ad_id", (int)$keywords);
            }
            else{
                $model->where("ad_account like '%$keywords%'");
            }
        }

        if(isset($input['start_date']) && $input['start_date'] !== ''){
            $start_time = strtotime($input['start_date']);
            $model->where("log_time >= $start_time");
        }

        if(isset($input['end_date']) && $input['end_date'] !== ''){
            $end_time = strtotime($input['end_date']);
            $model->where("log_time <= $end_time");
        }

        $list = $model->order("log_id desc")->paginate(null, false, [
                'query' => $input
            ]);
        foreach ($list as $k=>$v){
            $v['log_detail'] = AdminLogsModel::logDetail($v);
            $list[$k] = $v;
        }
        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
        ];
        return $this->fetch('index', $data);
    }
}
