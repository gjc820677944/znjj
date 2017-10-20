<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/20
 * Time: 11:33
 */
//作为接口传输的时候认证的密钥
$valid_token = 'd49dfa762268687eb2ca59498ce852';
//调用接口被允许的ip地址
$valid_ip = array('192.168.1.1','10.17.10.175','112.112.112.112');
$client_token = $_GET['token'];
$client_ip = $_SERVER['REMOTE_ADDR'];
$fs = fopen('./auto_hook.log', 'a');
fwrite($fs, 'Request on ['.date("Y-m-d H:i:s").'] from ['.$client_ip.']'.PHP_EOL);
if ($client_token !== $valid_token)
{
    echo "error 10001";
    fwrite($fs, "Invalid token [{$client_token}]".PHP_EOL);
    exit(0);
}
//if ( ! in_array($client_ip, $valid_ip))
//{
//    echo "error 10002";
//    fwrite($fs, "Invalid ip [{$client_ip}]".PHP_EOL);
//    exit(0);
//}
$json = file_get_contents('php://input');
$data = json_decode($json, true);
fwrite($fs, 'Data: '.print_r($data, true).PHP_EOL);
fwrite($fs, '======================================================================='.PHP_EOL);
$fs and fclose($fs);
//这里也可以执行自定义的脚本文件update.sh，脚本内容可以自己定义。
//exec("/bin/sh /root/updategit.sh");
exec("cd /data/mall;/usr/bin/git pull");