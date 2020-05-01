<?php

namespace DIT\RabbitMQBundle\Service;

/**
 * Interface EmitterServiceInterface
 */
interface EmitterServiceInterface
{
    public function emitMessage(string $message, string $routingKey);
}
