<?php
namespace app\admin\controller\user;
use app\admin\controller\Base;

class User extends Base
{
    public function index()
    {
            $data = [];
            return $this->fetch('index', $data);

    }

    public function userIinfo()
    {


    }

}
