<?php
namespace umeng;

require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSListcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSBroadcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSFilecast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSGroupcast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSUnicast.php');
require_once(dirname(__FILE__) . '/' . 'notification/ios/IOSCustomizedcast.php');

/**
 * 友盟消息通知类
 * Class Umeng
 * @package umeng
 */
class UmengIOS
{
    protected static $helper = null;
    protected $appkey = null;
    protected $appMasterSecret = null;
    protected $timestamp = null;
    protected $validation_token = null;

    private function __construct($key, $secret) {
		$this->appkey = $key;
		$this->appMasterSecret = $secret;
		$this->timestamp = strval(time());
	}

	public static function instance(){
        if(self::$helper === null){
            self::$helper = new self(UmengConfig::$ios_app_key, UmengConfig::$ios_app_secret);
        }
        return self::$helper;
    }

    /**
     * 发送IOS单播
     * @param string $device_tokens 设备Token，多个token之间用,分割
     * @param string $ticker 通知栏提示文字
     * @param string $custom 自定义内容
     * @return bool
     */
    public function sendIOSUnicast($device_tokens, $ticker, $custom) {
        try {
            $unicast = new IOSUnicast();
            $unicast->setAppMasterSecret($this->appMasterSecret);
            $unicast->setPredefinedKeyValue("appkey",           $this->appkey);
            $unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $unicast->setPredefinedKeyValue("device_tokens",    $device_tokens);
            $unicast->setPredefinedKeyValue("alert", $ticker);
            $unicast->setPredefinedKeyValue("badge", 0);
            $unicast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $unicast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields
            $unicast->setCustomizedField("custom", $custom);
            //print("Sending unicast notification, please wait...\r\n");
            $unicast->send();
            //print("Sent SUCCESS\r\n");
            return true;
        } catch (Exception $e) {
            //print("Caught exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 发送IOS广播
     * @param string $ticker 通知栏提示文字
     * @param string $title 通知标题
     * @param string $text 通知文字描述
     */
    public function sendIOSBroadcast($ticker, $title, $text) {
        try {
            $brocast = new \IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey",           $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp",        $this->timestamp);

            $brocast->setPredefinedKeyValue("alert", json_encode([
                'ticker'=>$ticker,
                'title' => $title,
                'text' => $text,
            ]));
            $brocast->setPredefinedKeyValue("badge", 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // Set customized fields
            $brocast->setCustomizedField("test", "helloworld");
            //print("Sending broadcast notification, please wait...\r\n");
            $brocast->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 发送IOS列播
     * @param string $device_tokens 设备Token，多个token之间用,分割
     * @param string $ticker 通知栏提示文字
     * @param string $custom 自定义内容
     * @return bool
     */
    function sendIOSListcast($device_tokens, $ticker, $custom) {
        try {
            $listcast = new \IOSListcast();
            $listcast->setAppMasterSecret($this->appMasterSecret);
            $listcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $listcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $listcast->setPredefinedKeyValue("device_tokens", $device_tokens);
            $listcast->setPredefinedKeyValue("alert", $ticker);
            $listcast->setPredefinedKeyValue("badge", 0);
            $listcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $listcast->setPredefinedKeyValue("production_mode", "true");
            // Set customized fields
            $listcast->setCustomizedField("custom", $custom);
            //print("Sending unicast notification, please wait...\r\n");
            $res = $listcast->send();
            //dump(true);dump($res);
            return true;
        } catch (Exception $e) {
            return false;
        }

        try {
            $listcast = new \IOSListcast();
            $listcast->setAppMasterSecret($this->appMasterSecret);
            $listcast->setPredefinedKeyValue("appkey",           $this->appkey);
            $listcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
            // Set your device tokens here
            $listcast->setPredefinedKeyValue("device_tokens", $device_tokens);
            $listcast->setPredefinedKeyValue("alert", $ticker);
            $listcast->setPredefinedKeyValue("badge", 0);
            $listcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $listcast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields
            $listcast->setCustomizedField("custom", $custom);
            //print("Sending unicast notification, please wait...\r\n");
            $res = $listcast->send();
            //dump(false);dump($res);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
