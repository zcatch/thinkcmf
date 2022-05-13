<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\demo\controller;

use api\common\lib\mq\Rabbitmq;
use cmf\controller\HomeBaseController;

class IndexController extends HomeBaseController
{
    public function index()
    {
        try {
            $mqModel   = Rabbitmq::getInstance();
            $newResult = ['hello', 'a', 'c'];
            if ($mqModel) {
                $mqRoute    = 'push_data_to_crm_routing';  // 路由
                $mqExchange = 'push_data_to_crm_exchange';  // 交换机
                $mqQuery    = 'push_data_to_crm_queue';  // 队列
                // 建立连接，设置交换机，设置队列
                $mqModel->setExchange($mqExchange, AMQP_EX_TYPE_DIRECT, AMQP_DURABLE)->setQueue($mqQuery, AMQP_DURABLE, $mqExchange, $mqRoute);
                foreach ($newResult as $k => $v) {
                    $push_data = $v;
                    $mqModel->publishMessage($push_data, $mqRoute); // 消息推送
                }
            }
        } catch (\Exception $e) {
            dump($e->getMessage());
            die;
        }
        echo 1;
        die();
        $isPushed = \think\Queue::push("app\job\Test@fun", [
            'id' => 123,
        ], 'test_queue');
        dump($isPushed);
        die;
        return $this->fetch(':index');
    }

    public function block()
    {
        return $this->fetch();
    }

    public function ws()
    {
        return $this->fetch(':ws');
    }
}
