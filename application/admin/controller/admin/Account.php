<?php

namespace app\admin\controller\admin;

use app\admin\controller\Base;

class Account extends Base
{

    public function index()
    {
        $data = [];
        return $this->fetch('index', $data);
    }


    public function create()
    {
        $data = [];
        return $this->fetch('edit', $data);
    }


    public function save()
    {

    }


    public function edit($id)
    {
        $data = [];
        return $this->fetch('edit', $data);
    }


    public function update()
    {

    }

    public function delete($id)
    {

    }
}
