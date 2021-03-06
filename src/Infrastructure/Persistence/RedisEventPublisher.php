<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Event\Subscriber;
use Redis;

class RedisEventPublisher implements Subscriber
{
    /** @var Redis */
    private $redis;

    /**
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Handles a DomainEvent by publishing it on a redis channel
     *
     * @param Event $event
     */
    public function handle(Event $event): void
    {
        $this->redis->publish('events', json_encode($event, JSON_THROW_ON_ERROR));
    }
}