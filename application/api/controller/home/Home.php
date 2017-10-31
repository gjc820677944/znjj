<?php
namespace app\api\controller\home;
use app\api\controller\Father;
use app\common\model\home\HomeDeviceProductModel;
use  app\common\model\home\HomeLeaguerInviteModel;
use app\common\model\home\HomeLeaguerModel;
use app\common\model\home\HomeModel;
use app\common\model\user\UserModel;
use think\Db;


class Home extends Father
{
    //邀请成员
    public function invitation()
    {
        $home_id=input('home_id');  //家庭ID
        $mobile=input('mobile');//被邀请人手机号
        $token=UserModel::getToken();

        if ($home_id==''){
            echo api_return_json(1,'家庭ID不能为空');
        }

        if ($mobile==''){
            echo api_return_json(1,'手机号不能为空');
        }

        if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
            echo api_return_json(1,'请输入正确手机号');
        }

        $info=UserModel::where("mobile='".$mobile."'")->find();
        if (empty($info)){
            echo api_return_json(1,'手机号不存在');
        }

        //判断邀请人是否有邀请他人的权限
        $leaguer_id=UserModel::where("token='".$token."'")->find();

        $auth=HomeLeaguerModel::where('home_id='.$home_id." and leaguer_id=".$leaguer_id['user_id'])->find();

        //如果是这个家庭的管理员就不用判断权限
        $admin=HomeModel::where('home_id='.$home_id." and creater_id=".$leaguer_id['user_id'])->find();
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

        try{
            $data['leaguer_id']=$info['user_id'];
            $data['home_id']=$home_id;
            $data['create_time']=time();
            $info=HomeLeaguerInviteModel::insert($data);

            echo api_return_json(0,'邀请成功');
        }catch (\Exception $e){
            echo api_return_json(1,'邀请失败');
        }
    }

    //家庭列表
    public function getHomeInfo()
    {
        $token=UserModel::getToken();

        try{
            $data=UserModel::where("token='".$token."'")->find();
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
    //家庭成员列表
    public function getHomeMember()
    {
        $home_id=input('home_id');
        if ($home_id==''){
            echo api_return_json(1,'家庭ID不能为空');
        }
        try{
            $data=HomeLeaguerModel::alias('hl')->where('home_id='.$home_id)->field('hl.*,su.username')
                ->join('smart_user su','hl.leaguer_id=su.user_id','left')
                ->select();
        }catch (\Exception $e){
            echo api_return_json(1,$e->getMessage());
        }
        echo api_return_json(0,$data);

    }

    //给成员分配权限
    public function allotAuth()
    {
        $auth_i           =   input('auth_i');      //邀请人的权限
        $auth_h           =   input('auth_h');      //是能家居的使用权限
        $leaguer_id     =   input('leaguer_id');    //成员ID

        if ($auth_i=='' || $leaguer_id==''){
            echo api_return_json(1,"成员ID或权限不能为空");
        }

        $data['auth']=$auth_i.",".$auth_h;
        $info=HomeLeaguerModel::where('leaguer_id='.$leaguer_id)->update($data);
        if ($info!==false){
            echo api_return_json(0,"修改成功");
        }else{
            echo api_return_json(1,"修改失败");
        }
    }





}





