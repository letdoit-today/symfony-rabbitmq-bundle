<?php

namespace DIT\RabbitMQBundle\Service;

use Exception;

/**
 * Class AbstractEmitterService
 */
abstract class AbstractDirectEmitterService extends AbstractRabbitMQService implements EmitterServiceInterface
{
    /**
     * @param string $message
     * @param string $routingKey
     * @throws Exception
     */
    public function emitMessage(string $message, string $routingKey)
    {
        $this->emitRabbitMQMessage($message, $routingKey);
    }

    protected function getType(): string
    {
        return 'direct';
    }
}
