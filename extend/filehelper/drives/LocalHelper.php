<?php
namespace filehelper\drives;

/**
 * 本地文件助手类
 * @author 周伟杰
 */
class LocalHelper extends BaseHelper
{
    public $prefixDir = "uploads/"; //文件存储的前置目录（默认文件都存储在前置目录下）
    protected $rootDir = "";   //文件允许保存的根目录（默认为当前目录,不包含前置目录）
    protected $siteUrl = "http://192.168.31.227";   //访问文件的网站地址

    /**
     * 存储上传的普通文件
     * @param File $file 待存储的文件
     * @param string $dir 文件将要保存的目录名称（在前置目录下）
     * @return string 目录下的相对路径或空字符串(为空字符串时，表示文件存储失败)
     */
    public function saveUploadFile($file, $dir = ''){
        if(empty($file)){
            return '';
        }

        if($file['error'] > 0) {
            return '';
        }
        $cur_datetime = date("YmdHis"); //当前时间
        $rand_num = mt_rand(1000, 9999); //随机数
        $file_basename =  $cur_datetime . $rand_num; //文件名

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION); //获取文件后缀
        $file_dir = $this->prefixDir . $dir . '/'; //相对保存目录
        $abs_file_dir = $this->rootDir . $file_dir; //绝对保存目录
        if (!is_dir($abs_file_dir)){
            mkdir($abs_file_dir, 0777, true); // 使用最大权限0777创建文件
            chmod($abs_file_dir, 0777);
        };

        $filename = $file_dir . $file_basename. '.' . $extension; // 获取需要创建的文件名称
        $abs_filename = $abs_file_dir . $file_basename . '.' . $extension;
        if(!move_uploaded_file($file['tmp_name'], $abs_filename)) {
            return '';
        }
        return $filename;
    }

    /**
     * 删除上传文件
     * @param string $filepath 上传文件的相对路径
     * @return bool
     */
    public function unlink($file_path){
        if(empty($file_path)){
            return false;
        }
        $abs_file_path = $this->rootDir . $file_path;
        if(file_exists($abs_file_path)){
            unlink($abs_file_path);
            return true;
        }
        return false;
    }

    /**
     * 移动本地文件到指定目录
     * @param string $local_path 文件本地路径
     * @param string $to_path 移动后的文件路径
     * @return bool
     */
    public function moveLocalFileTo($local_path, $to_path){
        $local_abs_path = $this->rootDir . $local_path; //文件绝对路径
        if(!file_exists($local_abs_path)){
            return false;
        }
        $to_abs_path = $this->rootDir . $to_path; //文件绝对路径
        $to_abs_dir = dirname($to_abs_path); //文件目录
        if(!is_dir($to_abs_dir)){
            mkdir($to_abs_dir, 0777, true);
        }
        $result = rename($local_abs_path, $to_abs_dir);
        if($result){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 复制本地文件到指定目录
     * @param string $local_path 文件本地路径
     * @param string $to_path 移动后的文件路径
     * @return bool
     */
    public function copyLocalFileTo($local_path, $to_path){

        $local_abs_path = $this->rootDir . $local_path; //文件绝对路径
        if(!file_exists($local_abs_path)){
            return false;
        }
        $to_abs_path = $this->prefixDir . $to_path; //文件绝对路径
        $to_abs_dir = dirname($to_path); //文件目录
        if(!is_dir($to_abs_dir)){
            mkdir($to_abs_dir, 0777, true);
        }
        $result = copy($local_abs_path, $to_path);
        if($result){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 移动文件位置
     * @param string $from_path 原文件路径
     * @param string $to_path 新文件路径
     * @return bool
     */
    public function moveFile($from_path, $to_path){
        if(!file_exists($from_path)){
            return false;
        }
        $from_abs_path = $this->rootDir . $from_path;
        $to_abs_path = $this->rootDir . $to_path;
        $to_abs_dir = dirname($to_abs_path);
        if(!is_dir($to_abs_dir)){
            mkdir($to_abs_dir, 0777, true);
        }
        $result = rename($from_abs_path, $to_abs_path);
        if($result){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 存储远程文件
     * @param string $file_url 文件远程地址
     * @param string $dir 文件存储目录
     * @param string $type 文件类型
     * @param string 文件相对路径或空字符串
     */
    public function saveWebFile($file_url, $dir, $type = "jpg"){
        $data = http($file_url);
        if(empty($data)){
            return '';
        }
        $cur_datetime = date("YmdHis"); //当前时间
        $rand_num = rand(1000, 9999); //随机数
        $file_basename =  $cur_datetime . $rand_num; //文件名
        $file_path = $dir.'/'.$file_basename.'.'.$type; //文件存储地址

        $re_file_path = $this->prefixDir.$file_path; //相对路径
        $abs_file_path = $this->rootDir.$re_file_path; //绝对路径
        $abs_file_dir = dirname($abs_file_path);
        if (!is_dir($abs_file_dir)){
            mkdir($abs_file_dir, 0777, true); // 使用最大权限0777创建文件
            chmod($abs_file_dir, 0777);
        };
        //存储到本地环境
        $result = file_put_contents($abs_file_path, $data);
        if($result){
            return $re_file_path;
        }
        else{
            return '';
        }
    }
}
