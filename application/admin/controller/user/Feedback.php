<?php
namespace app\admin\controller\user;
use app\admin\controller\Base;
use app\common\model\user\UserFeedbackModel;
use think\Exception;
use filehelper\FileHelper;

class Feedback extends Base
{
    public function index()
    {
        $input = $this->request->param();
        $model = new UserFeedbackModel();
        if (isset($input['keywords']) && $input['keywords'] !== '') {
            $keywords = $input['keywords'];
            $model->where("su.username like '%$keywords%'");
        }

        $list = $model->alias('uf')->field('uf.feedback_id,su.username,su.mobile,uf.content,uf.times,uf.pic')
                ->join('smart_user su','uf.user_id=su.user_id','left')
                ->paginate(null);

        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'create_url' => url('create'),
        ];
        return $this->fetch('', $data);
    }

    //反馈内容详情
    function detailed(){
        $input = $this->request->param();
        try{
            if (!isset($input['feedback_id']) && $input['feedback_id']==''){
                $this->error("要查看的ID不能为空");
            }
            $data=UserFeedbackModel::alias('uf')->field('uf.feedback_id,su.username,su.mobile,uf.content,uf.times,uf.pic')
                ->where('uf.feedback_id='.$input['feedback_id'])
                ->join('smart_user su','uf.user_id=su.user_id','left')
                ->find();

                $pic=explode(',',$data['pic']);
                foreach ($pic as $k=>$p){
                    $pic[$k]=FileHelper::helper()->getWebsitePath($p);
                    echo $pic[$k];
                }
                $data['pic']=$pic;
                $data['times']=date('Y-m-d H:i:s',$data['times']);
            $data = [
                'data' => $data,
            ];
            return $this->fetch('edit', $data);
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
    }

    //删除反馈
    function delete(){
        $input = $this->request->param();
        if (!isset($input['feedback_id']) && $input['feedback_id']==''){
            $this->error("要删除的ID不能为空");
        }
        $info=UserFeedbackModel::where('feedback_id='.$input['feedback_id'])->find();
        if ($info['pic']!=''){
            $pic=explode(',',$info['pic']);
            foreach ($pic as $p){
                FileHelper::helper()->unlink($p);
            }
        }

        $info=UserFeedbackModel::where('feedback_id='.$input['feedback_id'])->delete();
        if ($info!==false){
            api_return_json(0,"删除成功");
        }else{
            api_return_json(1, "删除失败");
        }
    }
}


    


