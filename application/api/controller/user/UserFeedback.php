<?php
namespace app\api\controller\user;
use app\api\controller\Father;
use app\common\model\user\UserFeedbackModel;
use app\common\model\user\UserModel;
use filehelper\FileHelper;
use think\Request;
class UserFeedback extends  Father
{

    //反馈图片上传
    function feedback_up_pic(){
        $file = Request::instance()->file('pic'); //获取上传的图片);
        $token=UserModel::getToken();
        $pic_data=cache($token);
        if (!empty($file)) {
            //保存图片到本地并拿到返回的存储地址
            $path= FileHelper::helper()->saveUploadFile($file->getInfo(), 'user/feedback/' . date("Ymd"));
            $pic_data[]=$path;
            $data['pic']=FileHelper::helper()->getWebsitePath($path);
            cache($token,$pic_data);
            echo api_return_json(0, $data);
        }
        echo api_return_json(1, '请上传图片');
    }

    //记录用户反馈
    function feedback(){
        $input = $this->requset->param();
        //不能全部为空
        if ((isset($input['pic']) && $input['pic']=='') && $input['content']==''){
            echo api_return_json(1, '反馈内容和反馈图片必填一项');
        }
        /****
         * 安卓上传是每次点击图片 都会上传到服务器，所以要缓存记录每次的图片和最后的图片比较，然后删除没有那些没有最后保存的图片
         * ios是在本地选择好图片 ，最好上传 不用缓存比较 type=1就是ios上传
         */
        if (isset($input['pic']) && $input['pic']!=''){
            $token=UserModel::getToken();
            $pic_data=cache($token);//已经上传的图片
            if (!isset($input['type'])){
                $new_pic=explode(',',$input['pic']);//用户筛选过的图片
                $count=count($pic_data);
                for($i=0;$i<$count;$i++){
                    $path_pic=FileHelper::helper()->getWebsitePath($pic_data[$i]);
                    //删除用户删除的图片
                    if (!in_array($path_pic,$new_pic)){
                        FileHelper::helper()->unlink($pic_data[$i]);
                        unset($pic_data[$i]);
                    }
                }
                $data['pic']=implode(',',$pic_data);                       //安卓
            }else{
                $data['pic']= str_replace(FileHelper::helper()->siteUrl."/","",$input['pic']); //ios
            }

            cache($token,null);//清除图片缓存
        }
        //字符不能大于251 数据库是255
        if (isset($input['content']) && strlen($input['content'])>251){
            echo api_return_json(1, '最多只能输入251个字符');
        }

        $data['times']=time();
        $data['user_id']=UserModel::getTokenId();

        $content=json_encode($input['content']);
        $content=preg_replace("/\\\u[ed][0-9a-f]{3}\\\u[ed][0-9a-f]{3}/","",$content);

        $data['content']=json_decode($content);
        try{
            UserFeedbackModel::create($data);
            echo api_return_json(0,"反馈成功");
        }catch (\Exception $e){
            echo api_return_json(1, $e->getMessage());
        }
    }

    //返回联系号码
    public function feedback_phone(){
//        $phone=web_config('feedback_phone')==false?'000-00000':web_config('feedback_phone');
        $data['phone']=web_config('feedback_phone');
        echo api_return_json(0, $data);
    }

}