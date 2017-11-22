<?php
namespace app\api\controller\user;
use app\api\controller\Father;
use app\common\model\user\UserFeedbackModel;
use app\common\model\user\UserModel;
use filehelper\FileHelper;
use think\Request;
class UserFeedback extends  Father
{
    //记录用户反馈
    function feedback(){
        $input = $this->requset->param();
        $file = Request::instance()->file('pic'); //获取上传的头像);
        //不能全部为空
        if (empty($file) && !isset($input['content'])){
            echo api_return_json(1, '反馈内容和反馈图片必填一项');
        }

        //字符不能大于251 数据库是
        if (isset($input['content']) && strlen($input['content'])>251){
            echo api_return_json(1, '最多只能输入251个字符');
        }

        $pic=array();
        if (!empty($file)) {
            $count=count($file);
            if ($count>3){
                echo api_return_json(1, '只能上传3张图片');
            }

            for ($i=0;$i<$count;$i++){
                //保存图片到本地并拿到返回的存储地址
                $pic[$i] = FileHelper::helper()->saveUploadFile($file[$i]->getInfo(), 'user/feedback/' . date("Ymd"));
            }
            $input['pic']=json_encode($pic);
        }

        $input['times']=time();
        $input['user_id']=UserModel::getTokenId();
        try{
            $info=UserFeedbackModel::create($input);
            echo api_return_json(0, '反馈成功');
        }catch (\Exception $e){
            //如果插入数据库失败  就删除上传的图片
            foreach ($pic as $p){
                FileHelper::helper()->unlink($p);
            }
            echo api_return_json(1, $e->getMessage());
        }
    }


}