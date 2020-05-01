<?php

namespace DIT\RabbitMQBundle\Service;

use ErrorException;
use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * Class AbstractRabbitMQService
 */
abstract class AbstractRabbitMQService implements RabbitMQServiceInterface
{
    protected $params;

    /**
     * TokenProvider constructor.
     * @param ContainerBagInterface $params
     */
    public function __construct(ContainerBagInterface $params)
    {
        $this->params = $params;
    }

    abstract protected function getExchange(): string;

    abstract protected function getType(): string;

    protected function getPassive(): bool
    {
        return false;
    }

    protected function getDurable(): bool
    {
        return false;
    }

    protected function getAutoDelete(): bool
    {
        return true;
    }

    protected function getExclusive(): bool
    {
        return true;
    }

    /**
     * @param string $message
     * @param string $routingKey
     * @throws Exception
     */
    protected function emitRabbitMQMessage(string $message, string $routingKey)
    {
        $connection = $this->createRabbitMQConnection();
        $channel = $connection->channel();
        $channel->exchange_declare(
            $this->getExchange(),
            $this->getType(),
            $this->getPassive(),
            $this->getDurable(),
            $this->getAutoDelete()
        );

        $rabbitMQMessage = new AMQPMessage($message);
        $channel->basic_publish($rabbitMQMessage, $this->getExchange(), $routingKey);

        $channel->close();
        $connection->close();
    }

    /**
     * @param callable $callback
     * @param array $routingKeys
     * @throws ErrorException
     * @throws Exception
     */
    protected function receiveRabbitMQMessage(callable $callback, array $routingKeys)
    {
        $connection = $this->createRabbitMQConnection();
        $channel = $connection->channel();
        $channel->exchange_declare(
            $this->getExchange(),
            $this->getType(),
            $this->getPassive(),
            $this->getDurable(),
            $this->getAutoDelete()
        );

        [$queueName, ,] = $channel->queue_declare(
            '',
            $this->getPassive(),
            $this->getDurable(),
            $this->getExclusive(),
            $this->getAutoDelete()
        );

        foreach ($routingKeys as $routingKey) {
            $channel->queue_bind($queueName, $this->getExchange(), $routingKey);
        }

        $channel->basic_consume($queueName, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    protected function createRabbitMQConnection()
    {
        $config = $this->params->get('letdoittoday.rabbitmq');
        $host = $config['host'];
        $port = $config['port'];
        $username = $config['username'];
        $password = $config['password'];

        return new AMQPStreamConnection($host, $port, $username, $password);
    }
}
