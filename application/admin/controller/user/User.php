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
//        $input=$this->request->param();
////        $keywords = $input['keywords'];
        $list = Db::name('user')->paginate(10,true,[
            'type'     => 'bootstrap',
            'var_page' => 'page',
        ]);
        $this->assign('data',$list);
        return $this->fetch();

    }

    public function userIinfo()
    {


    }

}
