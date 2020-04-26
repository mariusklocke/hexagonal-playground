<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\DBAL\Connection;
use HexagonalPlayground\Infrastructure\Persistence\DatabaseConfigurator;

class DoctrineConfigurator extends DatabaseConfigurator
{
    public function getName(): string
    {
        return 'doctrine';
    }

    public function validate(): void
    {
        $this->container->get(Connection::class);
    }
}