<?php
namespace app\api\controller\user;
use app\common\model\user\UserModel;
use app\api\controller\Father;
use think\Request;
class User extends  Father
{
    /**
     *登录&注册
     */
    public function login()
    {

        $user=new UserModel();
        $mobile   =   input('mobile');  //登录名
        $v_code     =   input('v_code');    //验证码
        if ($mobile==''){
            echo api_return_json(1,'手机号不能为空');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }

//        if ($v_code=='')
//        {
//            echo api_return_json(1,'验证码不能为空');
//        }
        $info=$user->Login($mobile);
        if ($info!==0){
            echo api_return_json(0,array('token'=>$info));
        }else{
            echo api_return_json(1,'登录失败');
        }
    }

    /**
     * 修改手机号码
     */
    public function updateMobile()
    {
        $user=new UserModel();

        $used_mobile     =   input('used_mobile');    //旧手机号
        $new_mobile     =   input('new_mobile');     //新手机号
        $v_code     =   input('v_code');    //验证码
        if ($used_mobile=='' || $new_mobile==''){
            echo api_return_json(1,'手机号不能为空');
        }
        if ($used_mobile == $new_mobile){
            echo api_return_json(1,'两个号码不能相同');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$used_mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }
        if(!preg_match("/^1[34578]{1}\d{9}$/",$new_mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }

//        if ($v_code==''){
//            echo api_return_json(1,'验证码不能为空');
//        }

        $info=$user->updateMobile($used_mobile,$new_mobile);
        if ($info===false) {
            echo api_return_json(1,"修改失败");
        }else{
            echo api_return_json(0);
        }
    }

    //编辑用户姓名
    function editUserName()
    {
        $user=new UserModel();
        $token=getToken();
        $username=input('username');
        if (empty($token)){
            echo api_return_json(1,"token不能为空");
        }
        if ($username==''){
            echo api_return_json(1,"用户名不能为空");
        }

        $info=$user->editUserName($token,$username);
        if($info===false){
            echo api_return_json(1,"修改失败");
        }else{
            echo api_return_json(0);
        }
    }

    //获取用户信息
    public function getUserInfo()
    {
        $user=new UserModel();
        $token=getToken();
        if (empty($token)){
            echo api_return_json(1,"token不能为空");
        }

        $info=$user->getUserInfo($token);
        if (empty($info)){
            echo api_return_json(1,"没有获取到数据");
        }else{
            echo api_return_json(0,$info);
        }
    }

    //退出登录
    public function logout()
    {
        $user=new UserModel();
        $token=getToken();

        if (empty($token)){
            echo api_return_json(1,"token不能为空");
        }

        $info=$user->logout($token);
        if ($info===false){
            echo api_return_json(1,"操作失败");
        }else{
            echo api_return_json(0);
        }

    }

    //图片上传
//    function upPic(Request $request)
//    {
//        $user=new UserModel();
//        $file=$request->file('pic');
//        if (true!==$this->validate(['image'=>$file],['image'=>'require|image']))
//        {
//            $this->error('请选择图像文件');
//        }
//        $image=Image::open($file);
//
//        $token=getToken();
//        if (empty($token)){
//            echo api_return_json(1,"token不能为空");
//        }
//
//        $info=$file->validate(['ext'=>'jpg,png,jpeg'])->move(ROOT_PATH.'public'.DS.'uploads');
//        if ($info){
//            $info=$user->upPic($info->getRealPath(),$token);
//            if ($info===false){
//                echo api_return_json(1,"上传失败");
//            }else{
//                echo api_return_json(0);
//            }
//        }else{
//            echo api_return_json(1,$file->getError());
//        }
//    }

}
