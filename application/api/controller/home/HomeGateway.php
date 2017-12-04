<?php
namespace app\api\controller\home;
use app\api\controller\Father;
use app\common\model\home\HomeGatewayModel;
use app\common\model\user\UserModel;


class HomeGateway extends Father
{
    /**
     * 添加家庭网关
     */
   function addHomeGateway()
   {
       $input = $this->requset->param();

       $data['add_time']=time();                //添加时间
       $data['user_id']=UserModel::getTokenId();//用户
       $data['ip']=$input['ip'];            //网关ip
       $data['port']=$input['port'];        //网关端口
       $data['home_id']=$input['home_id'];   //要设置的家庭

       if (isset($input['name'])){
           $data['name']=$input['name'];   //网关名称
       }

       //如果这个家庭已有网关那就修改
       $info=HomeGatewayModel::where('home_id='.$input['home_id'])->find();
       if (empty($info)){
           $info=HomeGatewayModel::create($data);
       }else{
           $info=HomeGatewayModel::where('home_id='.$input['home_id'])->update($data);
       }


        if ($info){
             api_return_json(0, '添加成功');
        }else{
             api_return_json(1, '添加失败');
        }
   }


    /***
     *获取家庭网关
     */
    function getHomeGateway()
    {
        $input = $this->requset->param();
        $data=HomeGatewayModel::where('home_id='.$input['home_id'])->field('gateway_id,ip,port,home_id,name')->find();
        if (!empty($data)){
            api_return_json(0, $data);
        }else{
            api_return_json(1, "没获取到数据 请稍后重试");
        }
    }


    /**
     * 返回mqtt域名
     */
    function getMqttServer()
    {
        $data['mqtt_server']=web_config('mqtt_server');
        echo api_return_json(0, $data);

    }


}





