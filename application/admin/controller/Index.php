<?php
namespace app\admin\controller;

class Index extends Base
{
    public function index()
    {
        return redirect("device.deviceModel/index");
    }
}