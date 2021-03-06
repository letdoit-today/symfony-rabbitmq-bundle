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
                $this->handleDefault($routingKey, $msg->body);
            }
        };

        $this->receiveRabbitMQMessage($callback, $this->getRoutingKeys());
    }

    protected function getType(): string
    {
        return 'direct';
    }

    protected function handleDefault(string $routingKey, string $body)
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
        $str = str_replace('_', ' ', $str);
        $str = str_replace('.', ' ', $str);

        return str_replace(' ', '', ucwords($str));
    }
}
