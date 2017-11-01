<?php
namespace app\api\controller\user;
use app\common\model\user\UserModel;
use app\api\controller\Father;
use think\Request;
use filehelper\FileHelper;
use think\Cache;
class User extends  Father
{
    /**
     *登录&注册
     */
    public function login()
    {
        $user = new UserModel();
        $mobile = input('mobile');  //登录名
        $v_code = input('v_code');    //验证码
        if ($mobile == '') {
            echo api_return_json(122, '手机号不能为空');
        }
        if (!preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
            echo api_return_json(121, '手机号格式不对');
        }

        if ($v_code != '123456') {
            echo api_return_json(102, '验证码错误');
        }
        $info = $user->Login($mobile);
        if ($info !== 0) {
            echo api_return_json(0, $info);
        } else {
            echo api_return_json(1, '登录失败');
        }
    }

    //登录验证码
    function getv_code()
    {
        $mobile = input('mobile');  //手机号
        if (!preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
            echo api_return_json(121, '手机号格式不对');
        }

        echo api_return_json(0, "验证码发送成功");
    }

    /**
     * 修改手机号码
     */
    public function updateMobile()
    {
        $user = new UserModel();

        $token = UserModel::getToken();

        $mobile = input('mobile');     //新手机号
        $v_code = input('v_code');    //验证码

        if (!preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
            echo api_return_json(121, '手机号格式不对');
        }

        if ($v_code!='123456'){
            echo api_return_json(102,'验证码错误');
        }

        $info = $user->updateMobile($mobile,$token);
        if ($info === false) {
            echo api_return_json(124, "手机号修改失败，请重新尝试");
        } else {
            echo api_return_json(0);
        }
    }

    //编辑用户姓名
    function editUserName()
    {
        $user = new UserModel();
        $token = UserModel::getToken();
        $username = input('username');

        if ($username == '') {
            echo api_return_json(112, "用户名格式不正确");
        }

        if(strlen($username)<3 || strlen($username)>11){
            echo api_return_json(112, "用户名长度3-11");
        }

        //判断用户名不重复
        $info=$user->where("username='".$username."'")->select();
        if (count($info)>1){
            api_return_json(112, "用户名已存在");
        }

        $info = $user->editUserName($token, $username);
        if ($info === false) {
            echo api_return_json(113, "用户名修改失败，请重新尝试");
        } else {
            echo api_return_json(0);
        }
    }

    //获取用户信息
    public function getUserInfo()
    {
        $user = new UserModel();
        $token = UserModel::getToken();

        $info = $user->getUserInfo($token);
        if (empty($info)) {
            echo api_return_json(145, "获取用户信息失败");
        } else {
            echo api_return_json(0, $info);
        }
    }

    //退出登录
    public function logout()
    {
        $user = new UserModel();
        $token = UserModel::getToken();

        if ($token==''){
            api_return_json(106, "token不能为空");
        }

        $info = $user->logout($token);
        if ($info === false) {
            echo api_return_json(132, "退出失败");
        } else {
            echo api_return_json(0);
        }
    }

    //修改用户头像
    public function updateInfiPic()
    {
        $token = UserModel::getToken();

        $file = Request::instance()->file('avatar'); //获取上传的头像
        $info = UserModel::where("token='" . $token . "'")->find();
        if (!empty($file)) {
            $input['avatar'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'user/avatar/' . date("Ymd"));
            UserModel::rmAvatarByid($info['user_id']);//删除原图片
        }
        $data['avatar'] = $input['avatar'];
        $info = UserModel::where("token='" . $token . "'")->update($data);
        if ($info === false) {
            echo api_return_json(125, "头像修改失败，请重新尝试");
        } else {
            echo api_return_json(0, "修改成功");
        }
    }

    //添加微信数据
    function getWeixinInfo()
    {
        $user = new UserModel();
        $access_token = input("access_token");      //token
        $wx_openid = input('wx_openid');     //用户微信ID
        $wx_unionid = input('wx_unionid','');   //用户微信联合ID
        if ($wx_openid == '' || $access_token == '') {
            echo api_return_json(105, "openid或access_token不能为空");
        }

        //获取用户微信息
//        $token="eMFmfYsbaqAoqbfj8XVPZiJ0z70o_TIYNHf3bkK2TmCToO_2Liy6wA-M0HXaV7dISy1ItP9OcjuO3ApUTjdDzla6UICZgzb4mmWpB-dpxh1lXZWkxUkOFqY_QGJd3uw3JTWiAJAVFI";
//        $wx_openid="orryGwi9BZp8GQQMWpvxl-g_KEkI";
//        $wx_unionid="";
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $access_token . "&openid=" . $wx_openid . "&lang=zh_CN ";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $output = json_decode($output, true);
        curl_close($ch);
        //下载微信图片保存到本地
        $info = $user->saveWeixinInfo($output, $wx_openid, $wx_unionid, $output['nickname']);
        if ($info === 0) {
            echo api_return_json(107, "微信登录失败，请重新尝试");
        } else {
            echo api_return_json(0, $info);
        }
    }


    //发送验证码
    function sendEmailCode()
    {
        $email=input('email');

        $mode = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        if(!preg_match($mode,$email)){
            echo api_return_json(111, "邮箱格式不正确");
        }

        $value='';
        for ($i=0;$i<6;$i++){
            $value.=mt_rand(0,9);
        }

        $subject="验证码";
        $message=$value;
        $addressee="820677944@qq.com";

        Cache::set($email,$value);
        if(sendEmail($email,'验证码',$value)){
            api_return_json(0, "验证码发送成功");
        }else{
            api_return_json(1, "验证码发送失败");
        }
    }

    //绑定邮箱
    function bindingEmail()
    {
        $v_code=input('v_code');//验证码
        $email=input('email');  //邮箱

        $old_code=Cache::get($email);
        if ($v_code!=$old_code){
            echo api_return_json(102, "验证码错误");
        }

        //判断邮箱是否存在
        $info=UserModel::where("email='".$email."'")->find();
        if (!empty($info)){
            echo api_return_json(102, "邮箱已存在");
        }

        $data['email']=$email;
        $user_id=UserModel::getTokenId();


        $info=UserModel::where('user_id='.$user_id)->update($data);
        if ($info!==false){
            Cache::set($email,null);
            echo api_return_json(0, "绑定成功");
        }else{
            echo api_return_json(126, "邮箱绑定失败");
        }
    }

}