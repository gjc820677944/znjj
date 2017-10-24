<?php

namespace app\admin\controller\admin;

use think\Controller;

class Login extends Controller
{
    public function index(){
        $data = [
            'post_url' => url('check'),
        ];
        return $this->fetch("index", $data);
    }

    public function check(){

    }
}
