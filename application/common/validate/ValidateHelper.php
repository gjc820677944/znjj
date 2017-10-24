<?php
namespace app\common\validate;

class ValidateHelper
{
    /**
     * 执行通用验证器操作
     * @param string $class_name 验证器类名
     * @param string $scene 验证器场景
     * @param array $input 输入数据
     * @return true|string
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