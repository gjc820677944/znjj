<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $request;

    protected function _initialize()
    {
        parent::_initialize();
        $this->request = Request::instance();
    }
}
