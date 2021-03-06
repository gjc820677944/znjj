<?php
namespace app\admin\controller\home;
use app\admin\controller\Base;
use app\common\model\admin\AdminOperationLogsModel;
use app\common\model\home\HomeModel;
use app\common\model\home\HomeLeaguerModel;
use think\Model;
use think\Request;
use filehelper\FileHelper;
use think\Db;


class Home extends Base
{
    public function index()
    {
        $input = $this->request->param();
        $model = new HomeModel();
        if (isset($input['keywords']) && $input['keywords'] !== '') {
            $keywords = $input['keywords'];
            $model->where("h.home_name like '%$keywords%'");
        }
        $field = "h.*,count(sh.leaguer_id) as personnel,su.username as c_username";
        $list = $model->alias('h')->field($field)
            ->join('smart_user su','su.user_id=h.creater_id','left')
            ->join('smart_home_leaguer sh','sh.home_id=h.home_id','left')
            ->group('h.home_id')
            ->paginate(null);

        $data = [
            'list' => $list,
            'page' => $list->render(),
            'input' => $input,
            'create_url' => url('create'),
        ];

        return $this->fetch('', $data);

    }

    public function create()
    {
        $input = $this->request->param();
        $model = new HomeModel();
        $data = $model->alias('h')->where('h.home_id ='.$input['home_id'])
            ->field("h.*,su.username as c_username")
            ->join('smart_user su','su.user_id=h.creater_id','left')
            ->find();
        if(empty($data)){
            $this->error("家庭不存在，请重新选择");
        }
        $data['wallpaper'] = FileHelper::helper()->getWebsitePath($data['wallpaper']);

        $field = "su.user_id as l_user_id,su.username as l_username,".
            "hl.remark,hl.create_time,hl.auth";
        $data['leaguer'] = HomeLeaguerModel::alias('hl')->field($field)
            ->where('hl.home_id='.$data['home_id'])
            ->join('smart_user su','hl.leaguer_id=su.user_id','left')
            ->select();

        //家庭成员
        $drop_down=HomeLeaguerModel::alias('hl')
            ->field('su.user_id,ifnull(su.username,su.mobile) as username')
            ->where('hl.home_id='.$data['home_id'])
            ->join('smart_user su','hl.leaguer_id=su.user_id','left')
            ->select();

        $data = [
            'drop_down'=>$drop_down,
            'data' => $data,
            'post_url' => url('save'),
        ];
        return $this->fetch('edit', $data);
    }

    //修改家庭信息
    public function updateHomeInfo()
    {
        $input = $this->request->param();
        $referer_url=$input['referer_url'];

        //修改家庭信息
        $data['home_id'] = $input['home_id'];
        $data['home_name'] = $input['home_name'];
        $data['creater_id'] = $input['user_id'];
        //存储上传的壁纸
        $file = Request::instance()->file('wallpaper'); //获取上传的头像
        if(!empty($file)){
            $data['wallpaper'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'home/wallpaper/'.date("Ymd"));
        }
        HomeModel::update($data);

        //修改家庭成员信息
        foreach ($input['info'] as $info){
            $arr['remark']=$info['remark'];
            $arr['auth']=$info['auth1'].",".$info['auth2'];
            HomeLeaguerModel::where('leaguer_id='.$info['user_id']." and home_id=".$input['home_id'])->update($arr);
        }

        AdminOperationLogsModel::log("更新用户家庭信息[id:".$input['home_id']."]");
        $this->success("修改成功", $referer_url);

    }

}





