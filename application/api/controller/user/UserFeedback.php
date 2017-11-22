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
//        if (!empty($pic_data)){
//            if (count($pic_data)>=9){
//                echo api_return_json(1, '只能上传9张图片');
//            }
//        }

        if (!empty($file)) {
            //保存图片到本地并拿到返回的存储地址
            $path= FileHelper::helper()->saveUploadFile($file->getInfo(), 'user/feedback/' . date("Ymd"));
            $pic_data[]=$path;
            cache($token,$pic_data,3600);
            $data['pic']=FileHelper::helper()->getWebsitePath($path);
            echo api_return_json(0, $data);
        }
        echo api_return_json(1, '请上传图片');
    }

    //记录用户反馈
    function feedback(){
        $input = $this->requset->param();
        //不能全部为空
        if (!isset($input['pic']) && !isset($input['content'])){
            echo api_return_json(1, '反馈内容和反馈图片必填一项');
        }
        //删除图片
        if (isset($input['pic']) && $input['pic']!=''){
            $token=UserModel::getToken();
            $pic_data=cache($token);//已经上传的图片
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
            $input['pic']=json_encode(implode(',',$pic_data));
            cache($token,null);
        }

        //字符不能大于251 数据库是255
        if (isset($input['content']) && strlen($input['content'])>251){
            echo api_return_json(1, '最多只能输入251个字符');
        }

        $input['times']=time();
        $input['user_id']=UserModel::getTokenId();
        try{
            $info=UserFeedbackModel::create($input);
            echo api_return_json(0, '反馈成功');
        }catch (\Exception $e){
            echo api_return_json(1, $e->getMessage());
        }
    }

    public function feedback_phone(){
        $phone=web_config('feedback_phone')==false?'000-00000':web_config('feedback_phone');
        $data['phone']=web_config('feedback_phone');
        echo api_return_json(0, $data);
    }

}