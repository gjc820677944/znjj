<?php
namespace app\admin\controller\user;
use app\admin\controller\Base;
use app\common\model\admin\AdminModel;
use app\common\model\user\UserModel;
use think\Request;
use filehelper\FileHelper;
use think\Db;

class User extends Base
{
    public function index()
    {

        $input = $this->request->param();
        $model = new UserModel();
        if (isset($input['keywords']) && $input['keywords'] !== '') {
            $keywords = $input['keywords'];
            $model->where("username like '%$keywords%'")
                ->whereOr("mobile like '%$keywords%'")
                ->whereOr("email like '%$keywords%'");
        }
        if (isset($input['status']) && $input['status'] !== '') {
            $status = (int)$input['status'];
            $model->where("status", $status);
        }
        $list = $model->paginate(null);
        foreach ($list as $k=>$v){
            $v['avatar'] = "/".$v['avatar'];
            $list[$k] = $v;
        }
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
        if ($input['type'] == 1) {
            $data = [
                'user_id' => 0,
                'username' => '',
                'mobile' => '',
                'email' => '',
                'status' => 1,
                'avatar' => '',
            ];
        } else {

            $data = UserModel::where('user_id=' . $input['user_id'])
                ->field('user_id,username,mobile,email,status,avatar')
                ->find();
            $data['avatar']="/".$data['avatar'];
        }
        $data = [
            'data' => $data,
            'post_url' => url('save'),
            'type' => $input['type'],
        ];
        return $this->fetch('edit', $data);
    }

    //添加用户或修改
    public function UserInfo()
    {

        $input = $this->request->param();
//        var_dump($input);exit;
        //如果user_id等于0就是添加 反之修改
        if ($input['user_id'] == 0) {
            unset($input['user_id']);
            User::addUser($input);
        } else {
            User::updateUser($input);
        }

    }

    //添加用户信息
    public function addUser($input)
    {
        $referer_url=$input['referer_url'];
        unset($input['referer_url']);
        $user=new UserModel();
        if ($input['mobile'] == '') {
            $this->error("手机号不能为空");
        }

        if (!preg_match("/^1[34578]{1}\d{9}$/", $input['mobile'])) {
            $this->error("手机号不能为空");
        }
        //存储上传的图片
        $file = Request::instance()->file('avatar'); //获取上传的头像
        if (!empty($file)) {
            $input['avatar'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'user/avatar/' . date("Ymd"));
        }
            $info=$user->addHtUserInfo($input);
            if ($info==1){
                $this->error("手机号已存在");
            }else if($info==0){
                $this->error("添加失败");
            }else if($info==2){
                $this->success("添加成功",$referer_url);
            }
    }


    //修改用户信息
    public function updateUser($input)
    {
        $referer_url=$input['referer_url'];
        unset($input['referer_url']);
        $user=new UserModel();
        if ($input['mobile'] == '') {
            $this->error("手机号不能为空");
        }

        if (!preg_match("/^1[34578]{1}\d{9}$/", $input['mobile'])) {
            $this->error("手机号不能为空");
        }
        $file = Request::instance()->file('avatar'); //获取上传的头像
        if (!empty($file)) {
            $input['avatar'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'user/avatar/' . date("Ymd"));
            UserModel::rmAvatarByid($input['user_id']);//删除原图片
        }
//        var_dump($input);exit;
        $info=$user->htUpdateUInfo($input);
        if ($info==1){
            $this->error("手机号已存在");
        }else if($info==0){
            $this->error("修改失败");
        }else if($info==2){
            $this->success("修改成功",$referer_url);
        }
    }

    //删除用户
    function deleteUserInfo()
    {
        $input = $this->request->param();
        $user_id=$input['user_id'];
        $info=UserModel::where('user_id='.$user_id)->delete();
        if ($info==false){
            $this->error("删除失败");
        }else{
            AdminModel::rmAvatarByid($user_id);     //删除头像
            $this->success("删除成功");
        }
    }
}


    


