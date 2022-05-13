<?php

namespace app\command;

use api\common\lib\mq\Rabbitmq;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 文件功能
 * Consume.php
 *
 * @category PHP
 * @author   zhengqs
 * @date     2022/4/15 15:39
 */
class Consume extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        // 指令配置
        $this->setName('mq')
            ->addArgument("work", Argument::REQUIRED, "work类型：a|b")
            ->setDescription("Do ticket cron");
    }

    protected function execute(Input $input, Output $output)
    {
        $type = trim($input->getArgument('work'));
        if ($type == 'a') {
            $this->consume1($output);
        }
        if ($type == 'b') {
            $this->consume2($output);
        }
    }

    /**
     * Consume constructor.
     *
     * @param \think\console\Output $output
     */
    protected function consume1(Output $output)
    {
        try {
            $mqModel = Rabbitmq::getInstance();
            // $mqRoute = 'push_data_to_crm_routing'; 消费者用不上路由，因为不需要指定。 只要想取队列，消费即可。
            $mqExchange = 'push_data_to_crm_exchange';
            $mqQuery    = 'push_data_to_crm_queue';
            $mqModel->setExchange($mqExchange, '', AMQP_PASSIVE)->setQueue($mqQuery, AMQP_PASSIVE);
            $mqModel->consume(function ($msg) {
                echo "消费者1：----处理中".PHP_EOL;
                sleep(50);
                var_dump($msg);
                echo "消费者1：----处理完成".PHP_EOL;
                return true;
            });
        } catch (\Exception $e) {
            dump($e->getMessage());
            die;
        }
        echo 'success';
        die;
    }

    protected function consume2(Output $output)
    {
        try {
            $mqModel = Rabbitmq::getInstance();
            // $mqRoute = 'push_data_to_crm_routing'; 消费者用不上路由，因为不需要指定。 只要想取队列，消费即可。
            $mqExchange = 'push_data_to_crm_exchange';
            $mqQuery    = 'push_data_to_crm_queue';

            $mqModel->setExchange($mqExchange, '', AMQP_PASSIVE)->setQueue($mqQuery, AMQP_PASSIVE);
            $mqModel->consume(function ($msg) {
                echo "消费者2：----处理中".PHP_EOL;
                sleep(5);
                var_dump($msg);
                echo "消费者2：----处理完".PHP_EOL;
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