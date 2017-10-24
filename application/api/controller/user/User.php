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


    //添加微信数据
    function getWeixinInfo() 
    {
        $user=new UserModel();
//        $access_token=  input("access_token");      //token
//        $wx_openid   =   input('wx_openid');     //用户微信ID
//        $wx_unionid  =   input('wx_unionid');   //用户微信联合ID
//        if ($wx_openid=='' || $wx_unionid=='' || $access_token==''){
//            echo api_return_json(1,"openid,unionid或access_token不能为空");
//        }

        //获取用户微信息
        $token="QXKENKC2yNqpUOXFrDHr1uKIuzbM_YQm-2iXwTiR4g8iX4CQpGlM3skrDhvExjfPloLQYTobtOaVXg8C5dWBPYWIwxE7XCEOItTPkldMSFnviNvCTZ-pkiQABw0kl_g6OWSeADADLH";
        $wx_openid="orryGwi9BZp8GQQMWpvxl-g_KEkI";
        $wx_unionid="";
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$wx_openid."&lang=zh_CN ";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $output=json_decode($output,true);
        curl_close($ch);
        //下载微信图片保存到本地
        $avatar=getImage($output['headimgurl'],"../public/uploads/weixin",time().'.jpg',1);
        $info=$user->saveWeixinInfo($avatar,$wx_openid,$wx_unionid,$output['nickname']);
        if ($info===0){
            echo api_return_json(1,"操作失败");
        }else{
            echo api_return_json(0,$info);
        }

    }


}
