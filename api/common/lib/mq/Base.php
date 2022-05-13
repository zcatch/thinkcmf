<?php
namespace api\common\lib\mq;

use think\facade\Config;

/**
 * 文件功能
 * Base.php
 *
 * @category PHP
 * @author   zhengqs
 * @date     2022/4/15 14:06
 */
class Base
{
    public $channel;
    public $exchange;
    public $changeName;
    public $changeType;
    public $queue;
    public $queueName;
    public $callBackFnc;
    public $isAct;

    public function __construct($config = [])
    {
        if (empty($Info)) {
            $config = Config::get('site.mq');
        }
        $connection = new \AMQPConnection($config);
        try {
            $exchangeName = 'demo';
            $routeKey     = 'hello';
            $message      = 'Hello World!';
            $connection->connect();
            $this->channel  = new \AMQPChannel($connection);
            $this->exchange = new \AMQPExchange($this->channel);
//            $this->exchange->setType(AMQP_EX_TYPE_DIRECT);
//            $this->exchange->declareExchange();
//            $this->exchange->publish($message, $routeKey);
        } catch (\AMQPConnectionException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}