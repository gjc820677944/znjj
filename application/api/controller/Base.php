<?php

namespace app\api\controller;



use think\Request;

class Base
{
    protected $requset;

    public function __construct()
    {
        $this->requset = Request::instance();
    }
}
