<?php
namespace Src;
class MyGreeter
{

    //3600秒
    const SECOND_CONSTANT = 3600;
    //早上
    const GOOD_MORNING = "Good morning";
    //下午
    const GOOD_AFTERNOON = "Good afternoon";
    //晚上
    const GOOD_EVENING = "Good evening";
    public function greeting()
    {
        // 设置为上海时区
        date_default_timezone_set('Asia/Shanghai');
        //当前时间戳
        $currentTime = time();
//        var_dump(date('Y-m-d H:i:s', $currentTime));
//        $currentTime = time() - 10 * 3600;
//        var_dump(date('Y-m-d H:i:s', $currentTime));
        //今天凌晨
        $todayPoint = strtotime(date('Y-m-d'));
        switch ($currentTime) {
            //6AM至12AM
            case $currentTime >= ($todayPoint + 6 * self::SECOND_CONSTANT) &&
                $currentTime <= ($todayPoint + 12 * self::SECOND_CONSTANT):
                $result = self::GOOD_MORNING;
                break;
            //12AM至6PM
            case  $currentTime >= ($todayPoint + 12 * self::SECOND_CONSTANT) &&
                $currentTime <= ($todayPoint + 18 * self::SECOND_CONSTANT):
                $result = self::GOOD_AFTERNOON;
                break;
            //6PM至第二天6AM
            case  $currentTime >= ($todayPoint + 18 * self::SECOND_CONSTANT) &&
                $currentTime <= ($todayPoint + 24 * self::SECOND_CONSTANT) ||
                $currentTime >= $todayPoint &&
                $currentTime <= ($todayPoint + 6 * self::SECOND_CONSTANT):
                $result = self::GOOD_EVENING;
                break;
            //兜底情景
            default:
                $result = '';
                break;
        }
        return $result;
    }
}

$a = new MyGreeter();
$result = $a->greeting();
var_dump($result);exit;