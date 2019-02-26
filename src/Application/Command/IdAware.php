<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\Util\Uuid;

trait IdAware
{
    /** @var string */
    private $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    private function setId($id): void
    {
        if (null === $id) {
            $this->id = Uuid::create();
            return;
        }
        TypeAssert::assertString($id, 'id');
        $this->id = $id;
    }
}