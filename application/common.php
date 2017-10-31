<?php
use think\Db;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
//define("USER_DEFAULT_AVATAR", "/uploads/admin/avatar/default.png"); //默认头像

/**
 * 定义接口返回数据格式
 * @param int $code 错误码 $code
 * @param null $data 返回数据或错误提示信息
 */
function api_return_json($code = 0, $data = null)
{
    header("Access-Control-Allow-Origin: http://localhost:8080");
    header("Access-Control-Allow-Credentials:true");
    if($code === 0){
        $msg = '请求成功';
    }
    else{
        $msg = $data;
        $data = null;
    }
    $result = [
        'code' => $code,
        'data' => $data,
        'msg' => $msg,
    ];
    exit(json_encode($result));
}

/*
   *功能：php完美实现下载远程图片保存到本地
   *参数：文件url,保存文件目录,保存文件名称，使用的下载方式
   *当保存文件名称为空时则使用远程文件原来的名称
   */
function getImage($url,$save_dir='',$filename='',$type=0)
{
    if(trim($url)==''){
        return array('file_name'=>'','save_path'=>'','error'=>1);
    }
    if(trim($save_dir)==''){
        $save_dir='./';
    }
    if(trim($filename)==''){//保存文件名
        $ext=strrchr($url,'.');
        if($ext!='.gif'&&$ext!='.jpg'){
            return array('file_name'=>'','save_path'=>'','error'=>3);
        }
        $filename=time().$ext;
    }
    if(0!==strrpos($save_dir,'/')){
        $save_dir.='/';
    }
    //创建保存目录
    if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
        return array('file_name'=>'','save_path'=>'','error'=>5);
    }
    //获取远程文件所采用的方法
    if($type){
        $ch=curl_init();
        $timeout=5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $img=curl_exec($ch);
        curl_close($ch);
    }else{
        ob_start();
        readfile($url);
        $img=ob_get_contents();
        ob_end_clean();
    }
    //$size=strlen($img);
    //文件大小
//        echo $save_dir;exit;
    $fp2=@fopen($save_dir.$filename,'a');
    fwrite($fp2,$img);
    fclose($fp2);
    unset($img,$url);
    return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
}

/**
 * 删除二位数组中指定的字段
 * @param array $arrs 二维数组
 * @param array $fields 需要删除的字段
 */
function array_remove_fields($arrs, $fields){
    if(is_string($fields)){
        $fields = explode(',', $fields);
    }
    foreach ($arrs as $k=>$v){
        foreach ($fields as $field)
        {
            if(isset($v[$field])){
                unset($v[$field]);
            }
        }
        $arrs[$k] = $v;
    }
    return $arrs;
}

/**
 * 发送HTTP请求方法
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http($url, $params = [], $method = 'GET', $header = array(), $multi = false){
    $opts = array(
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER     => $header
    );

    /* 根据请求类型设置特定参数 */
    switch(strtoupper($method)){
        case 'GET':
            $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
            break;
        case 'POST':
            //判断是否传输文件
            $params = $multi ? $params : http_build_query($params);
            $opts[CURLOPT_URL] = $url;
            $opts[CURLOPT_POST] = 1;
            $opts[CURLOPT_POSTFIELDS] = $params;
            break;
        default:
            throw new Exception('不支持的请求方式！');
    }

    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if($error) throw new Exception('请求发生错误：' . $error);
    return  $data;
}


/**
 * @param $addressee收件人
 * @param $subject标题
 * @param $message消息主题
 */
    function sendEmail($addressee,$subject,$message)
    {
        include ("../vendor/phpmailer/class.phpmailer.php");
        $mail = new \PHPMailer();           //实例化PHPMailer对象

        $mail->IsSMTP();                    // 设定使用SMTP服务
        $mail->CharSet ="UTF-8";    //编码s
        $mail->SMTPDebug = 0;               // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;             // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl';          // 使用安全协议
        $mail->Host = "smtp.qq.com";      // SMTP 服务器
        $mail->Port = 465;                  // SMTP服务器的端口号
        $mail->Username = "820677944@qq.com";    // SMTP服务器用户名
        $mail->Password = "olumlwjydtpcbbjj";     // SMTP服务器密码
        $mail->SetFrom('820677944@qq.com', 'yzm');
        $replyEmail = '';                   //留空则为发件人EMAIL
        $replyName = '';                    //回复名称（留空则为发件人名称）
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->MsgHTML($message);
        $mail->AddAddress($addressee, 'jw');
        return $mail->Send();

    }





