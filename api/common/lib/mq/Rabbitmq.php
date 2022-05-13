<?php

namespace api\common\lib\mq;

use api\common\lib\mq\Base;
use api\common\lib\TraitInstance;

/**
 * 文件功能
 * Rabbitmq.php
 *
 * @category PHP
 * @author   zhengqs
 * @date     2022/4/15 14:38
 */
class Rabbitmq extends Base
{
    use TraitInstance;

    /**
     * setExchange  设置交换机
     * User  zqs
     * Date  2022/4/15 14:59
     *
     * @param string $changeName
     * @param string $changeType
     * @param bool   $flags
     *
     * @return $this
     * @throws \Exception
     */
    public function setExchange($changeName = '', $changeType = '', $flags = false)
    {
        $errorMsg = '';
        try {
            if (!$this->channel) {
                throw new \AMQPQueueException("Error channel on method setExchange", 1);
            }
            $this->exchange = new \AMQPExchange($this->channel);
            if ($changeName) {
                $this->changeName = $changeName; // 交换机名称
                $this->exchange->setName($changeName); // 设置名称
                $changeType = $changeType ? $changeType : AMQP_EX_TYPE_DIRECT;  // 交换机类型
            } else {
                $this->changeName = '';
            }
            if ($changeType) {
                $this->changeType = $changeType;
                $this->exchange->settype($changeType);  // 设置交换机类型
            } else {
                $this->changeType = '';
            }
            if ($flags) {
                $this->exchange->setFlags($flags);  //交换机标志
            }
            if ($changeType || $flags) {
                $this->exchange->declareExchange();  // 创建
            }
        } catch (\AMQPQueueException $ex) {
            $errorMsg = "AMQPQueueException error exchange: {$ex->getMessage()},\r\nline: {$ex->getLine()}\r\n";
        } catch (\Exception $ex) {
            $errorMsg = "Exception error exchange:  {$ex->getMessage()},\r\nline: {$ex->getLine()}\r\n";
        }
        if ($errorMsg) {
            throw new \Exception($errorMsg, 1);
        }
        return $this;
    }

    /**
     * setQueue
     * User  zqs
     * Date  2022/4/15 15:22
     *
     * @param string $queueName
     * @param string $flags
     * @param string $exchange_name
     * @param string $routing_key
     * @param array  $arguments
     *
     * @return $this|bool
     * @throws \Exception
     */
    public function setQueue($queueName = '', $flags = '', $exchange_name = '', $routing_key = '', $arguments = [])
    {
        $errorMsg = '';
        try {
            if (!$this->channel) {
                throw new \AMQPQueueException("Error channel on method setQueue", 1);
            }
            $this->queue = new \AMQPQueue($this->channel);
            if (!$queueName) {
                return false;
            }
            $this->queueName = $queueName;  // 队列名称
            $this->queue->setName($queueName);
            if ($flags) {
                $this->queue->setFlags($flags);  // 队列标志。与消息持久化有关。 这篇文字不涉及这一块的说明
            }
            if (is_array($arguments) && !empty($arguments)) {
                $this->queue->setArguments($arguments);  // 参数配置
            }
            $this->queue->declareQueue();  // 创建一个队列
            $exchange_name = $exchange_name === false ?'' :($exchange_name === true || !$exchange_name ? $this->changeName : $exchange_name);

            $routing_key = $routing_key ? $routing_key : $this->queueName;

            if ($exchange_name && $routing_key) {
                $this->queue->bind($exchange_name, $routing_key);  // 交换机和队列的绑定操作
            }

        } catch (\AMQPQueueException $ex) {
            $errorMsg = "AMQPQueueException error queue: {$ex->getMessage()},\r\nline: {$ex->getLine()}\r\n";
        } catch (\Exception $ex) {
            $errorMsg = "Exception error queue:  {$ex->getMessage()},\r\nline: {$ex->getLine()}\r\n";
        }
        if ($errorMsg) {
            throw new \Exception($errorMsg, 1);
        }
        return $this;
    }

    /**
     * publishMessage   发布消息
     * User  zqs
     * Date  2022/4/15 15:05
     *
     * @param string $message
     * @param string $routing_key
     * @param int    $flags
     * @param array  $attributes
     *
     * @return bool
     * @throws \AMQPChannelException
     * @throws \AMQPConnectionException
     * @throws \AMQPExchangeException
     */
    public function publishMessage($message = '', $routing_key = '', $flags = AMQP_NOPARAM, $attributes = [])
    {
        if (!$message) {
            return false;
        }
        $routing_key = $routing_key ? $routing_key : $this->queueName;
        // 发布消息，带有路由key。如果需要，则会用于关联。
        $this->exchange->publish($message, $routing_key, $flags, $attributes);
        return true;
    }

    /**
     * consume  消费
     * User  zqs
     * Date  2022/4/15 14:57
     *
     * @param null $callback
     * @param int  $qos
     * @param bool $isAct
     *
     * @throws \Exception
     */
    public function consume($callback = null, $qos = 0, $isAct = true)
    {
        if ($qos) {
            $this->channel->qos(0, $qos);
        }
        $errorMsg = '';
        try {
            if (!$this->queue) {
                throw new \AMQPQueueException("Error queue on method consume", 1);
            }
            $this->callBackFnc = $callback;
            $this->isAct       = $isAct;
            $callback          = function ($envelope, $queue) {
                if (is_callable($this->callBackFnc)) {
                    call_user_func($this->callBackFnc, $envelope->getBody());
                    if ($this->isAct) {
                        $queue->ack($envelope->getDeliveryTag());
                    } else {
                        $queue->nack($envelope->getDeliveryTag());
                    }
                }
            };
            $this->queue->consume($callback);
        } catch (\AMQPQueueException $ex) {
            $errorMsg = "AMQPQueueException error queue: {$ex->getMessage()},\r\nline: {$ex->getLine()}\r\n";
        } catch (\Exception $ex) {
            $errorMsg = "Exception error queue:  {$ex->getMessage()},\r\nline: {$ex->getLine()}\r\n";
        }
        if ($errorMsg) {
            throw new \Exception($errorMsg, 1);
        }
    }
}