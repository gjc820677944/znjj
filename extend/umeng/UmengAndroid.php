<?php
namespace umeng;

require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidListcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/android/AndroidCustomizedcast.php');

/**
 * 友盟消息通知类
 * Class Umeng
 * @package umeng
 */
class UmengAndroid
{
    protected static $helper = null;
    protected $appkey = null;
    protected $appMasterSecret = null;
    protected $timestamp = null;
    protected $validation_token = null;

    protected function __construct($key, $secret) {
		$this->appkey = $key;
		$this->appMasterSecret = $secret;
		$this->timestamp = strval(time());
	}

	public static function instance(){
        if(self::$helper === null){
            self::$helper = new self(UmengConfig::$android_app_key, UmengConfig::$android_app_secret);
        }
        return self::$helper;
    }

    /**
     * 发送安卓单播
     * @param string $device_tokens 设备Token，多个token之间用,分割
     * @param string $ticker 通知栏提示文字
     * @param string $custom 自定义内容
     * @return bool
     */
    public function sendAndroidUnicast($device_tokens, $ticker, $custom) {
        try {
            $unicast = new \AndroidUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $device_tokens);
            $unicast->setPredefinedKeyValue("expire_time", date('Y-m-d H:i:s',time()+3600));
            $unicast->setPredefinedKeyValue("ticker",           $ticker['ticker']);
            $unicast->setPredefinedKeyValue("title",            $ticker['title']);
            $unicast->setPredefinedKeyValue("text",             $ticker['text']);

            $unicast->setPredefinedKeyValue("after_open",       "go_custom");
            $unicast->setPredefinedKeyValue("custom", $custom);
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $unicast->setPredefinedKeyValue("production_mode", "true");
            // Set extra fields
            $unicast->setExtraField("test", "helloworld");
            // print("Sending unicast notification, please wait...\r\n");
            $result = $unicast->send();
            // print("Sent SUCCESS\r\n");
            return true;
        } catch (Exception $e) {
            // print("Caught exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送安卓广播
     * @param string $ticker 通知栏提示文字
     * @param string $title 通知标题
     * @param string $text 通知文字描述
     */
    function sendAndroidBroadcast($ticker, $title, $text) {
        try {
            $brocast = new \AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker", $ticker);
            $brocast->setPredefinedKeyValue("title", $title);
            $brocast->setPredefinedKeyValue("text", $text);
            $brocast->setPredefinedKeyValue("after_open", "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // [optional]Set extra fields
            $brocast->setExtraField("test", "helloworld");
            //print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 发送安卓列播
     * @param string $device_tokens 设备Token，多个token之间用,分割
     * @param string $ticker 通知栏提示文字
     * @param string $custom 自定义内容
     * @return bool
     */
    function sendAndroidListcast($device_tokens, $ticker, $custom) {
        try {
            $listcast = new \AndroidListcast();
            $listcast->setAppMasterSecret($this->appMasterSecret);
            $listcast->setPredefinedKeyValue("appkey", $this->appkey);
            $listcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set your device tokens here
            $listcast->setPredefinedKeyValue("device_tokens", $device_tokens);
            $listcast->setPredefinedKeyValue("ticker", $ticker);
            $listcast->setPredefinedKeyValue("title", $ticker);
            $listcast->setPredefinedKeyValue("text", $ticker);
            $listcast->setPredefinedKeyValue("after_open", "go_custom");
            $listcast->setPredefinedKeyValue("custom", $custom);
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $listcast->setPredefinedKeyValue("production_mode", "true");
            // Set extra fields
            //$unicast->setExtraField("test", "helloworld");
            //print("Sending unicast notification, please wait...\r\n");
            $res = $listcast->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}
