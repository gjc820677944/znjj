<?php

namespace app\api\controller\user;

use app\api\controller\Base as CommonBase;
use app\common\model\user\UserModel;

class Base extends CommonBase
{
    protected $user;
    protected $user_id;

    public function __construct()
    {
        parent::__construct();
        $token = UserModel::getToken();
        $user = (new UserModel())->getUserInfo($token);
        if(empty($user)){
            api_return_json(11, "用户登录令牌错误");
        }
        $this->user = $user;
        $this->user_id = $user['user_id'];
    }
}
