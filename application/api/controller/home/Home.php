<?php
namespace app\api\controller\home;
use app\api\controller\Father;
use app\common\model\home\HomeDeviceProductModel;
use  app\common\model\home\HomeLeaguerInviteModel;
use app\common\model\home\HomeLeaguerModel;
use app\common\model\home\HomeModel;
use app\common\model\user\UserModel;
use app\common\model\user\UserUmeng;
use think\Db;
use umeng\Umeng;
use umeng\UmengAndroid;
use umeng\UmengIOS;


class Home extends Father
{
    //邀请成员
    public function invitation()
    {
        $home_id=input('home_id');  //家庭ID
        $mobile=input('mobile');//被邀请人手机号
        $user_id=UserModel::getTokenId();
        if ($home_id==''){
            echo api_return_json(1,'家庭ID不能为空');
        }

        if ($mobile==''){
            echo api_return_json(1,'手机号不能为空');
        }

        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }

        //判断自己不能邀请自己
        $user_info=UserModel::where("mobile='".$mobile."'")->find();
        if ($user_info['user_id']==$user_id){
            echo api_return_json(1,'自己不能邀请自己');
        }
        //判断邀请人是否有邀请他人的权限
        $auth=HomeLeaguerModel::where('home_id='.$home_id." and leaguer_id=".$user_id)->find();
        //如果是这个家庭的管理员就不用判断权限
        $admin=HomeModel::where('home_id='.$home_id." and creater_id=".$user_id)->find();
        if (empty($admin)){
            if (!empty($auth)){
                $auth=explode(',',$auth);
                if ($auth[1]=='N'){
                    echo api_return_json(1,'没有邀请人的权限');
                }
            }else{
                echo api_return_json(1,'获取邀请人权限失败');
            }
        }

        //判断是否邀请过了
        $if_exist=HomeLeaguerInviteModel::where('home_id='.$home_id." and leaguer_mobile=".$mobile)->find();
        if (!empty($if_exist)){
            echo api_return_json(1,'已经邀请过了');
        }

        try{
            $data['leaguer_mobile']=$mobile;
            $data['home_id']=$home_id;
            $data['create_time']=time();
            $data['inviter_id']=$user_id;
            $info=HomeLeaguerInviteModel::insert($data);
            $this->sendMessage($mobile,$user_id,$home_id);      //消息推送
            echo api_return_json(0,'邀请成功');
        }catch (\Exception $e){
            echo api_return_json(1,$e->getMessage());
        }
    }

    //给被邀请的人发消息
    function sendMessage($mobile,$user_id,$home_id)
    {
        //获取邀请人信息
        $inviter=UserModel::where('user_id='.$user_id)->find();
        if (empty($inviter)){
            return;
        }
       // 给被邀请的人发送短信

        //给app推送消息
        //获取该手机用户的信息
        $info=UserModel::where("mobile='".$mobile."'")->find();
        $mes['ticker']="hei";
        $mes['title']="邀请信";
        $mes['text']=$inviter['username']."(".$inviter['mobile']."）邀请你加入".$inviter['username']."的‘家’";
        $custom['me_type']=1;//自定义参数
        $custom['home_id']=$home_id;
        $custom['text']=$inviter['username']."(".$inviter['mobile']."）邀请你加入".$inviter['username']."的‘家’";
        //如果用户信息不为空 就去找该用户设备的token
        if (!empty($info)){
            //获取该用户的设备token
            $device_token=UserUmeng::where('user_id='.$info['user_id'])->find();
            if (!empty($device_token)){
                if ($device_token['android_device_token']!=''){
                    Umeng::sendUnicast($device_token['android_device_token'],$mes,1,$custom);
                }else{
                    Umeng::sendUnicast($device_token['ios_device_token'],$mes,2,$custom);
                }
            }
        }


    }

    //同意或拒绝
    function ynInvitation()
    {
        $data['home_id'] =input('home_id');//家庭 ID
        $user_id=UserModel::getTokenId();//登录人的ID
        $type   = input('type');        //1是同意 2是拒绝

        $user_info=UserModel::where("user_id=".$user_id)->find();
        if ($type==1){
            Db::startTrans();
            try{
                $data['create_time']=time();
                $data['leaguer_id']=$user_id;

                HomeLeaguerModel::insert($data);
                HomeLeaguerInviteModel::where('home_id='.$data['home_id']." and leaguer_mobile=".$user_info['mobile'])->delete();
            Db::commit();
                echo api_return_json(0,'加入成功');
            }catch (\Exception $e){
                Db::rollback();
                echo api_return_json(1,$e->getMessage());
            }
        }else{
            try{
                HomeLeaguerInviteModel::where('home_id='.$data['home_id']." and leaguer_mobile=".$user_info['mobile'])->delete();
                echo api_return_json(0,'操作成功');
            }catch(\Exception $e){
                echo api_return_json(1,$e->getMessage());
            }
        }
    }

    //删除成员
    function deleteMember()
    {
        $data['home_id'] =input('home_id');//家庭 ID
        $data['leaguer_id']=input('leaguer_id');//成员ID

        $info=HomeLeaguerModel::where("home_id=".$data['home_id']." and leaguer_id=".$data['leaguer_id'])->delete();
        if ($info!==false){
            echo api_return_json(0,'操作成功');
        }else{
            echo api_return_json(1,'操作失败');
        }
    }

    //家庭列表
    public function getHomeInfo()
    {
        $token=UserModel::getToken();

        try{
            $data=UserModel::where("token=".$token)->find();
            $home=HomeModel::where('creater_id='.$data['user_id'])->select();
            $count=count($home);
            for ($i=0;$i<$count;$i++){
                $home[$i]['tag']=HomeDeviceProductModel::where('home_id='.$home[$i]['home_id'])->column('tag');
            }
        }catch (\Exception $e){
            echo api_return_json(1,$e->getMessage());
        }
        echo api_return_json(0,$home);
    }
//
//    //家庭成员列表
//    public function getHomeMember()
//    {
//        $home_id=input('home_id');
//        if ($home_id==''){
//            echo api_return_json(1,'家庭ID不能为空');
//        }
//        try{
//            $data=HomeLeaguerModel::alias('hl')->where('home_id='.$home_id)->field('hl.*,su.username')
//                ->join('smart_user su','hl.leaguer_id=su.user_id','left')
//                ->select();
//        }catch (\Exception $e){
//            echo api_return_json(1,$e->getMessage());
//        }
//        echo api_return_json(0,$data);
//    }
//
//    //给成员分配权限
//    public function allotAuth()
//    {
//        $auth_i           =   input('auth_i');      //邀请人的权限
//        $auth_h           =   input('auth_h');      //是能家居的使用权限
//        $leaguer_id     =   input('leaguer_id');    //成员ID
//
//        if ($auth_i=='' || $leaguer_id==''){
//            echo api_return_json(1,"成员ID或权限不能为空");
//        }
//
//        $data['auth']=$auth_i.",".$auth_h;
//        $info=HomeLeaguerModel::where('leaguer_id='.$leaguer_id)->update($data);
//        if ($info!==false){
//            echo api_return_json(0,"修改成功");
//        }else{
//            echo api_return_json(1,"修改失败");
//        }
//    }




}





