<?php

namespace filehelper;
use filehelper\drives\LocalHelper;

/**
 * Class FileHelper
 * 文件助手类
 * @package filehelper
 * @author 周伟杰
 */
class FileHelper
{
    private static $helper = null;

    private function __construct()
    {

    }

    public static function helper(){
        if(self::$helper === null){
            self::$helper = new LocalHelper();
        }
        return self::$helper;
    }
}