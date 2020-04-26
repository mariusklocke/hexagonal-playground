<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Entity;
use JsonSerializable;

class Event extends Entity implements JsonSerializable
{
    public const DATE_FORMAT = 'Y-m-d';

    /** @var string */
    private $type;

    /** @var DateTimeImmutable */
    private $occurredAt;

    /** @var array */
    private $payload;

    /**
     * @param string $type
     * @param array $payload
     */
    public function __construct(string $type, array $payload)
    {
        parent::__construct(null);
        $this->type       = $type;
        $this->occurredAt = new DateTimeImmutable();
        $this->payload    = $payload;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'occurredAt' => $this->occurredAt->getTimestamp(),
            'payload' => $this->payload
        ];
    }
}