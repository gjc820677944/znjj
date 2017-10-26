<?php
namespace app\api\controller\user;
use app\common\model\user\UserModel;
use app\api\controller\Father;
use think\Request;
use filehelper\FileHelper;
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
        $token=UserModel::getToken();
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
        $token=UserModel::getToken();
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
        $token=UserModel::getToken();

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

    //修改用户头像
    public function updateInfiPic()
    {
        $token=input('token');
        if ($token==''){
            echo api_return_json(1,"token不能为空");
        }
        $file = Request::instance()->file('avatar'); //获取上传的头像
        $info=UserModel::where("token='".$token."'")->find();
        if (!empty($file)) {
            $input['avatar'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'user/avatar/' . date("Ymd"));
            UserModel::rmAvatarByid($info['user_id']);//删除原图片
        }
        $data['avatar']= $input['avatar'];
        $info=UserModel::where("token='".$token."'")->update($data);
        if ($info==false){
            echo api_return_json(1,"修改失败");
        }else{
            echo api_return_json(0,"修改成功");
        }

    }

    //添加微信数据
    function getWeixinInfo() 
    {
        $user=new UserModel();
        $access_token=  input("access_token");      //token
        $wx_openid   =   input('wx_openid');     //用户微信ID
        $wx_unionid  =   input('wx_unionid');   //用户微信联合ID
        if ($wx_openid=='' || $access_token==''){
            echo api_return_json(1,"openid或access_token不能为空");
        }

        //获取用户微信息
//        $token="eMFmfYsbaqAoqbfj8XVPZiJ0z70o_TIYNHf3bkK2TmCToO_2Liy6wA-M0HXaV7dISy1ItP9OcjuO3ApUTjdDzla6UICZgzb4mmWpB-dpxh1lXZWkxUkOFqY_QGJd3uw3JTWiAJAVFI";
//        $wx_openid="orryGwi9BZp8GQQMWpvxl-g_KEkI";
//        $wx_unionid="";
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$wx_openid."&lang=zh_CN ";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $output=json_decode($output,true);
        curl_close($ch);
        //下载微信图片保存到本地
        $info=$user->saveWeixinInfo($output,$wx_openid,$wx_unionid,$output['nickname']);
        if ($info===0){
            echo api_return_json(1,"操作失败");
        }else{
            echo api_return_json(0,$info);
        }

    }


}
