<?php
namespace app\common\validate;

class ValidateHelper
{
    /**
     * 执行通用验证器操作
     * @param $class_name
     * @param $scene
     * @param $input
     * @return array|bool
     */
    public static function execValidate($class_name, $scene, $input){
        $validate = validate($class_name);
        if(!$validate->scene($scene)->check($input)){
            $msg = $validate->getError();
            return $msg;
        }
        return true;
    }
}