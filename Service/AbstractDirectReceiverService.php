<?php

namespace DIT\RabbitMQBundle\Service;

use ErrorException;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class AbstractDirectReceiverService
 */
abstract class AbstractDirectReceiverService extends AbstractRabbitMQService implements ReceiverServiceInterface
{
    abstract protected function getRoutingKeys(): array;

    /**
     * @throws ErrorException
     */
    public function receiveMessage()
    {
        $callback = function (AMQPMessage $msg) {
            $routingKey = $msg->delivery_info['routing_key'];
            $camelizedRoutingKey = $this->camelize($routingKey);
            $functionName = "handle{$camelizedRoutingKey}Message";

            if (method_exists($this, $functionName)) {
                $this->{$functionName}($msg->body);
            } else {
                $this->handleDefault();
            }
        };

        $this->receiveRabbitMQMessage($callback, $this->getRoutingKeys());
    }

    protected function getType(): string
    {
        return 'direct';
    }

    protected function handleDefault()
    {
    }

    /**
     * Camelizes a given string.
     *
     * @param string $str
     * @return string
     */
    protected function camelize(string $str): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }
}
