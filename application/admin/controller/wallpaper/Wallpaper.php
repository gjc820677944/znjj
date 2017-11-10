<?php
namespace app\admin\controller\wallpaper;
use app\admin\controller\Base;
use app\common\model\device\DeviceWallpaperModel;
use \think\Request;
use filehelper\FileHelper;
use app\common\model\user\UserModel;

class Wallpaper extends Base
{
    function index()
    {
        $input = $this->request->param();
        $model = new DeviceWallpaperModel();

        $list = $model->paginate(null);

        foreach ($list as $k=>$v){
            $v['url'] = FileHelper::helper()->getWebsitePath($v['url']);
            $list[$k] = $v;
        }

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
        //1是添加墙纸
        if ($input['type'] == 1) {
            $data = [
                'wallpaper_id' => 0,
                'title' => '',
                'url' => '',
                'w_type' => '',
            ];
        } else {

            $data = DeviceWallpaperModel::where('wallpaper_id=' . $input['wallpaper_id'])->find();
            $data['url'] = FileHelper::helper()->getWebsitePath($data['url']);
        }

        $data = [
            'data' => $data,
            'post_url' => url('save'),
            'type' => $input['type'],
        ];
        return $this->fetch('edit', $data);
    }

    //添加或修改墙纸信息
    function  wallpaperInfo()
    {
        $input = $this->request->param();
        //大于0就是修改
        if ($input['wallpaper_id']>0){
                $this->updateWallpaper($input);
        }else{
                $this->addWallpaper($input);
        }
    }

    //修改墙纸信息
    function updateWallpaper($input)
    {
        $referer_url=$input['referer_url'];
        unset($input['referer_url']);

        //存储上传的图片
        $file = Request::instance()->file('url'); //获取上传的头像
        if (!empty($file)) {
            $input['url'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'device/qiangzhi/' . date("Ymd"));
            DeviceWallpaperModel::rmUrlByid($input['wallpaper_id']);//删除原图片
        }

        $info=DeviceWallpaperModel::update($input);
        if ($info!==false){
            $this->success("修改成功",$referer_url);
        }else{
            $this->error("修改失败");
        }


    }

    //添加墙纸信息
    function addWallpaper($input)
    {
        $referer_url=$input['referer_url'];
        unset($input['referer_url']);
        unset($input['wallpaper_id']);
        //存储上传的图片
        $file = Request::instance()->file('url'); //获取上传的头像
        if (!empty($file)) {
            $input['url'] = FileHelper::helper()
                ->saveUploadFile($file->getInfo(), 'device/qiangzhi/' . date("Ymd"));
        }

        $info=DeviceWallpaperModel::create($input);
        if ($info){
            $this->success("添加成功",$referer_url);
        }else{
            $this->error("添加失败");
        }




    }

}


    


