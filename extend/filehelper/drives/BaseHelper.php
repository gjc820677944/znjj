<?php
namespace filehelper\drives;

/**
 * 文件助手基础类
 * @author 周伟杰
 */
abstract class BaseHelper
{
    protected $prefixDir; //文件存储的前置目录（默认文件都存储在前置目录下）
    protected $rootDir;   //文件允许保存的根目录（默认为当前目录,不包含前置目录）
    protected $siteUrl;   //访问文件的网站地址

    /**
     * 存储上传的普通文件
     * @param File $file 待存储的文件
     * @param string $dir 文件将要保存的目录名称（在前置目录下）
     * @return string 目录下的相对路径或空字符串
     */
    abstract public function saveUploadFile($file, $dir = '');

    /**
     * 删除上传文件
     * @param string $filepath 上传文件的相对路径
     * @return bool
     */
    abstract public function unlink($filepath);

    /**
     * 移动本地文件到指定目录
     * @param string $local_path 文件本地路径
     * @param string $to_path 移动后的文件路径
     * @return bool
     */
    abstract public function moveLocalFileTo($local_path, $to_path);

    /**
     * 移动文件位置
     * @param string $from_path 原文件路径
     * @param string $to_path 新文件路径
     * @return bool
     */
    abstract public function moveFile($from_path, $to_path);


    /**
     * 获取文件网址路径（包含普通文件）
     * @param string $filepath 文件相对网址路径
     * @return string 包含网址的文件路径
     */
    public function getWebsitePath($filepath){
        if(empty($filepath)){
            return '';
        }
        $filepath = '/'.$filepath;
        $filepath = $this->siteUrl . $filepath;
        return $filepath;
    }

    /**
     * 获取文件的前置路径(用于校验文件是否为本站文件)
     * @param bool $is_website 是否包含网址
     * @return string
     */
    public function getPrefixPath($is_website = true){
        if(!$is_website){
            return $this->prefixDir;
        }
        return $this->siteUrl.'/'.$this->prefixDir;
    }

    /**
     * 去掉本站文件网址前缀
     * @param string $file_url 本站文件网址
     * @return string
     */
    public function deleteWebsitePath($file_url){
        $filepath = str_replace($this->siteUrl.'/', '', $url);
        return $filepath;
    }
}
