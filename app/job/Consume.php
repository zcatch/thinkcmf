<?php
/**
 * 文件功能
 * consume.php
 *
 * @category PHP
 * @author   zhengqs
 * @date     2022/4/15 15:13
 */

namespace app\job;

use api\common\lib\mq\Rabbitmq;

class Consume
{
    public function test()
    {
        try {
            dump(1111);die;
            $mqModel = Rabbitmq::getInstance();
            // $mqRoute = 'push_data_to_crm_routing'; 消费者用不上路由，因为不需要指定。 只要想取队列，消费即可。
            $mqExchange = 'push_data_to_crm_exchange';
            $mqQuery    = 'push_data_to_crm_queue';
            $mqModel->setExchange($mqExchange, '', AMQP_PASSIVE)->setQueue($mqQuery, AMQP_PASSIVE);
            $mqModel->consume(function ($msg) {
                var_dump($msg);
                return true;
            });
        } catch (\Exception $e) {
            dump($e->getMessage());
            die;
        }
        echo 'success';
        die;
    }
}