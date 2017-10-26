<?php
namespace app\admin\controller\Edition;
use app\common\model\edition\EditionModel;
use app\admin\controller\Base;
use think\Request;
use filehelper\FileHelper;
use think\Db;

class Edition extends Base
{
    public function index()
    {
        $input = $this->request->param();
        $model = new EditionModel();
        if (isset($input['keywords']) && $input['keywords'] !== '') {
            $keywords = $input['keywords'];
            $model->where("edition_number like '%$keywords%'");
        }

        $list = $model->paginate(null);

        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'create_url' => url('create'),
        ];
        return $this->fetch('', $data);

    }

    public function create()
    {
        $input = $this->request->param();
        //添加版本
        if ($input['type'] == 1) {
            $data = [
                'edition_id' => 0,
                'edition_number' => '',
                'edition_url' => '',
                'remarks' => '',
                'type' => '',
                'addtime' => 0,
            ];
        } else {

            $data = EditionModel::where('edition_id=' . $input['edition_id'])
                ->find();
        }
        $data = [
            'data' => $data,
            'post_url' => url('save'),
            'type' => $input['type'],
        ];
        return $this->fetch('edit', $data);
    }

    public function editionInfo()
    {
        $input = $this->request->param();
        //大于0就是添加修改
        if ($input['edition_id']>0){
            Edition::updateEdition($input);
        }else{
            unset($input['edition_id']);
            Edition::addEdition($input);
        }

    }
    //添加版本
    public function addEdition($input)
    {
        $referer_url=$input['referer_url'];
        unset($input['referer_url']);
        $input['addtime']=time();
        $info=EditionModel::insert($input);
        if ($info==false){
            $this->error("添加失败");
        }else{
            $this->success("添加成功",$referer_url);
        }
    }

    //修改版本
    public function updateEdition($input)
    {
        $referer_url=$input['referer_url'];
        unset($input['referer_url']);
        $input['addtime']=time();
        $info=EditionModel::where('edition_id='.$input['edition_id'])->update($input);
        if ($info==false){
            $this->error("修改失败");
        }else{
            $this->success("修改成功",$referer_url);
        }
    }

    //删除版本
    public function deleteEdition()
    {
        $input = $this->request->param();
        $info=EditionModel::where('edition_id='.$input['edition_id'])->delete();
        if ($info==false){
            api_return_json(1, "删除失败");
        }else{
            api_return_json(0, "删除成功");
        }
    }
}





