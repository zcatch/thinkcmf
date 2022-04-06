<?php

namespace app\job;

use api\common\lib\dingtalk\Robot;
use think\queue\Job;

/**
 * 文件功能
 * test.php
 *
 * @category PHP
 * @author   zhengqs
 * @date     2022/4/2 16:47
 */
class Test
{
    public function fun(Job $job, $data)
    {
        $this->sendDingTalk("队列数据异常", $data);
    }

    private function sendDingTalk($taskMsg, $queueData, $result = [])
    {
        $content = "## 【消息队列】  \n";
        $content .= "**错误信息：** {$taskMsg}  \n";
        $content .= "**脚本路径：** app/job/test  \n";
        $content .= "**队列数据：** " . json_encode($queueData, JSON_UNESCAPED_UNICODE) . "  \n";
        !empty($result) && $content .= "**任务执行结果：** " . (is_array($result) ? json_encode($result) : $result) . "  \n";
        try{
            Robot::getInstance()->setMsgType("markdown")->setTitle("消息队列错误提醒")->setContent($content)->sendToOptionGroup("test");
        }catch (\Exception $e){
            dump($e->getMessage());die;
        }
    }
}