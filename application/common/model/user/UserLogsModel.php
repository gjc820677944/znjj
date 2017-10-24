<?php

namespace app\common\model\user;

use think\Model;

class UserLogsModel extends Model
{
    protected $name = "user_logs";

    /**
     * 插入用户日志
     * user_id  用户ID
     * log_type 日志类型
     * log_ip  用户IP
     * log_data 日志数据
     * log_time日志时间
     */
    public static function addLog($user_id,$log_type,$log_ip,$log_time,$log_data='')
    {
        $data['user_id']=$user_id;
        $data['log_type']=$log_type;
        $data['log_ip']=$log_ip;
        $data['log_data']=$log_data;
        $data['log_time']=$log_time;
        $result = UserLogsModel::create($data);
        return $result ? true : false;
    }
}
