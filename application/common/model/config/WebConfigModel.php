<?php

namespace app\common\model\config;

use think\Model;

class WebConfigModel extends Model
{
    protected $name = "web_config";

    public static $typeTexts = [
        'text' => '单行文本',
        'textarea' => '多行文本',
    ];

    //生成配置文件
    public static function makeConfigFile(){
        $list = self::order("sort_by desc, conf_id asc")
            ->field("conf_key, conf_value")->select();
        $configs = [];
        foreach ($list as $v){
            $key = $v['conf_key'];
            $value = $v['conf_value'];
            $configs[$key] = $value;
        }
        $str = "<?php\r\n";
        $str .= "return [\r\n";
        $str .= "   'web_config' => ".var_export($configs, true);
        $str .= "\r\n];";
        $filename = APP_PATH.'common/config/web.php';
        file_put_contents($filename, $str);
    }
}
