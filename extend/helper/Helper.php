<?php
namespace  helper;

/**
 * Created by PhpStorm.
 * User: abi
 * Date: 2017/2/27
 * Time: 17:52
 */
class Helper{

    /**
     * 获取IP位置
     * @param $clientIP
     * @return string
     */
    static function get_ip_city($clientIP='192.168.1.245'){
        $taobaoIP = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$clientIP;
        $IPinfo = json_decode(file_get_contents($taobaoIP));
        $province = $IPinfo->data->region;
        $city = $IPinfo->data->city;
        $data = $province.$city;
        return $data;
    }

    /**
     * 验证手机号是否正确
     * @param $mobile 手机号码
     * @return bool
     */
    static function check_mobile($mobile){
        return preg_match("/^1[34578]{1}\d{9}$/",$mobile) ? true : false;
    }

    /**
     * 验证证件格式是否正确
     * @param $card 身份证
     * @return bool
     */
    static  function check_card($card,$type=0){
        switch ($type){
            case 0:
                //身份证
                if(preg_match("/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/",$card)){
                    return true;
                }else{
                    return preg_match("/^(?:\d{15}|\d{18})$/",$card) ? true : false;
                }
                break;
            case 1:
                //临时身份证
                return preg_match("/^(?:\d{15}|\d{18})$/",$card) ? true : false;
                break;
            case 2:
                //护照
                return preg_match("/(^[a-zA-Z]{5,17}$)|(^[a-zA-Z0-9]{5,17}$)/",$card) ? true : false;
                break;
            case 3:
                //军官证
                return preg_match("/^[a-zA-Z0-9]{7,21}$/",$card) ? true : false;
                break;
            case 4:
                //士兵证
                return preg_match("/^[a-zA-Z0-9]{7,21}$/",$card) ? true : false;
                break;
            case 5:
                //台胞证
                return preg_match("/(^[0-9]{8}$)|(^[0-9]{10}$)/",$card) ? true : false;
                break;
            case 6:
                //港澳通行证
                return preg_match("/^[HMhm]{1}([0-9]{10}|[0-9]{8})$/",$card) ? true : false;
                break;
            default:
                return false;
        }

    }

    /**
     *获取加密盐
     */
    static function get_salt(){
        return substr(uniqid(),0,6);
    }



    static function getmicrotime()
    {
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
     * @param int $start
     * @param int $end
     * @return array
     */
    static function get_year_list($start=1990,$end=0){
        if ($start > $end) {
            $start = 1990;
        }
        if (!$end && $end < $start) {
            $end = date('Y');
        }
        $list = [];
        for($end;$start<$end;$end--){
            $list[] = $end;
        }
        return $list;
    }

    /**
     * 随机字符
     * @param number $length 长度
     * @param string $type 类型
     * @param number $convert 转换大小写
     * @return string
     */
    public static function random($length=6, $type='string', $convert=0){
        $config = array(
            'number'=>'1234567890',
            'letter'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'string'=>'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789',
            'all'=>'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );

        if(!isset($config[$type])) $type = 'string';
        $string = $config[$type];

        $code = '';
        $strlen = strlen($string) -1;
        for($i = 0; $i < $length; $i++){
            $code .= $string{mt_rand(0, $strlen)};
        }
        if(!empty($convert)){
            $code = ($convert > 0)? strtoupper($code) : strtolower($code);
        }
        return $code;
    }

    static function export_csv($filename,$data){
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0'); header('Pragma:public');
        echo $data;
    }

    /**
     * 遍历文件夹
     * @param string $dir
     * @param boolean $all  true表示递归遍历
     * @return array
     */
    public static function scanfDir($dir='', $all = false, &$ret = array()){
        if ( false !== ($handle = opendir ( $dir ))) {
            while ( false !== ($file = readdir ( $handle )) ) {
                if (!in_array($file, array('.', '..', '.git', '.gitignore', '.svn', '.htaccess', '.buildpath','.project'))) {
                    $cur_path = $dir . '/' . $file;
                    if (is_dir ( $cur_path )) {
                        $ret['dirs'][] =$cur_path;
                        $all && self::scanfDir( $cur_path, $all, $ret);
                    } else {
                        $ret ['files'] [] = $cur_path;
                    }
                }
            }
            closedir ( $handle );
        }
        return $ret;
    }

    /**
     * 删除文件夹及其文件夹下所有文件
     */
    public static function delDir($dirName) {
        if(!is_dir($dirName))
        {
            return false;
        }
        $handle = @opendir($dirName);
        while(($file = @readdir($handle)) !== false)
        {
            if($file != '.' && $file != '..')
            {
                $dir = $dirName . '/' . $file;
                is_dir($dir) ? self::delDir($dir) : @unlink($dir);
            }
        }
        closedir($handle);
        return rmdir($dirName) ;
    }

    /**
     * 判断系统类型(android or ios)
     * @return string
     */
    public static function getDeviceType()
    {
        //全部变成小写字母
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $type = 'other';
        //分别进行判断
        if(strpos($agent, 'iphone') || strpos($agent, 'ipad'))
        {
            $type = 'ios';
        }

        if(strpos($agent, 'android'))
        {
            $type = 'android';
        }
        return $type;
    }

}