<?php
namespace app\admin\controller\home;

use app\admin\controller\Base;
use app\common\model\admin\AdminOperationLogsModel;
use app\common\model\home\HomeDeviceProductModel;
use app\common\model\home\HomeModel;
use filehelper\FileHelper;


class HomeDeviceProduct extends Base
{
    public function index()
    {
        $input = $this->request->param();
        $model = new HomeDeviceProductModel();
        if(isset($input["home_id"]) && $input['home_id'] !== ''){ //家庭ID
            $home_id = (int)$input['home_id'];
            $home = HomeModel::field("home_id, home_name")->find($home_id);
            $model->where("p.home_id", $home_id);
        }
        else{
            $home = null;
        }
        if(isset($input["model_id"]) && $input['model_id'] !== ''){ //模型ID
            $model_id = (int)$input['model_id'];
            $model->where("p.model_id", $model_id);
        }
        if(isset($input["is_gateway"]) && $input['is_gateway'] !== ''){ //是否为网关
            $is_gateway = (int)$input['is_gateway'];
            $model->where("p.is_gateway", $is_gateway);
        }
        if(isset($input["keywords"]) && $input['keywords'] !== ''){ //模型ID
            $keywords = trim($input['keywords']);
            $model->where("p.serial_number", "like", "%$keywords%");
        }

        $field = "p.*, m.model_name, m.model_number, m.model_cover, m.protocol, h.home_name";
        $list = $model->alias("p")->field($field)
            ->join("device_model m", "m.model_id = p.model_id", "left")
            ->join("home h", "h.home_id = p.home_id", "left")
            ->paginate(20, null, [
                    'query' => $input
                ]);
        foreach ($list as $k=>$v){
            $v['model_cover'] = FileHelper::helper()->getWebsitePath($v['model_cover']);
            $list[$k] = $v;
        }
        $data = [
            'list' => $list,
            'page' => $list->render(),
            'home' => $home,
            'input' => $input,
        ];
        return $this->fetch('', $data);

    }

    public function delete(int $id)
    {
        $result = HomeDeviceProductModel::destroy($id);
        if($result){
            AdminOperationLogsModel::log("删除用户家庭绑定设备[id:".$id."]");
            api_return_json(0);
        }
        else{
            api_return_json(101, "删除失败，请重新尝试");
        }
    }
}





