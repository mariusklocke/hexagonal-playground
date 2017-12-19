<?php

namespace HexagonalDream\Domain;

class Pitch
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var GeographicLocation */
    private $location;

    public function __construct(UuidGeneratorInterface $uuidGenerator, string $label, GeographicLocation $location)
    {
        $this->id = $uuidGenerator->generateUuid();
        $this->label = $label;
        $this->location = $location;
    }

    /**
     * @param UuidGeneratorInterface $uuidGenerator
     * @return Pitch
     */
    public function createCopy(UuidGeneratorInterface $uuidGenerator)
    {
        $clone = clone $this;
        $clone->id = $uuidGenerator->generateUuid();
        return $clone;
    }

    private function __clone()
    {
        $this->id = null;
    }
}
