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
        $list = $model->paginate(1);

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

       //1是添加用户
       if ($input['type']==1){
           $data = [
               'user_id' => 0,
               'username' => '',
               'mobile' => '',
               'email' => '',
               'status'     =>1,
               'avatar' =>  '',
           ];
       }else{

            $data=UserModel::where('user_id='.$input['user_id'])
                            ->field('user_id,username,mobile,email,status,avatar')
                            ->find();
       }
        $data = [
            'data' => $data,
            'post_url' => url('save'),
            'type'  =>  $input['type'],
        ];
        return $this->fetch('edit', $data);
    }

    //添加用户或修改
    public function UserInfo()
    {

        $input = $this->request->param();
        //如果user_id等于0就是添加 反之修改
        if ($input['user_id']==0){
            unset($input['user_id']);
            User::addUser($input);
        }else{
            User::updateUser($input);
        }

    }

    //添加用户信息
    public static function addUser($data)
    {


    }

    //修改用户信息
    public static function updateUser($data)
    {

    }

}
