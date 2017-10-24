<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $request;

    protected function __construct()
    {
        $this->request = Request::instance();
    }
}
