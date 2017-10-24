<?php

namespace app\admin\controller\admin;

use app\admin\controller\Base;

class Login extends Base
{
    public function index(){
        dump(123);
        return $this->fetch("index");
    }

    public function check(){

    }
}
