<?php
namespace app\admin\controller\user;
use app\admin\controller\Base;
use app\common\model\user\UserModel;
use think\Request;
use think\Db;

class User extends Base
{
    public function index()
    {

        $input = $this->request->param();
        $model = new UserModel();
        if(isset($input['keywords']) && $input['keywords'] !== ''){
            $keywords = $input['keywords'];
            $model->where("username like '%$keywords%'")
                ->whereOr("mobile like '%$keywords%'")
                ->whereOr("email like '%$keywords%'");
        }
        if(isset($input['status']) && $input['status'] !== ''){
            $status = (int)$input['status'];
            $model->where("status", $status);
        }
        $list = $model->paginate(10);

        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'create_url' => url('create'),
        ];

        $this->assign('data',$data);
        return $this->fetch();

    }

    public function create()
    {
        $data = [
            'ad_id' => 0,
            'ad_account' => '',
            'password' => '',
            'avatar' => '',
            'ad_name' => '',
            'email' => '',
            'status' => 0,
        ];
        $data = [
            'data' => $data,
            'post_url' => url('save'),
        ];
        return $this->fetch('edit', $data);
    }

    //添加用户
    public function addUser()
    {
        $input = $this->request->post();

    }

}
