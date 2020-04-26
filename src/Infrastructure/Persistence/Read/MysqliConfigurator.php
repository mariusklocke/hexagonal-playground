<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\DatabaseConfigurator;
use mysqli;

class MysqliConfigurator extends DatabaseConfigurator
{
    public function getName(): string
    {
        return 'mysqli';
    }

    public function validate(): void
    {
        $this->container->get(mysqli::class);
    }
}