<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\ServiceConfiguratorInterface;
use Psr\Container\ContainerInterface;

abstract class DatabaseConfigurator implements ServiceConfiguratorInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getDefaults(): array
    {
        return [
            'MYSQL_HOST' => 'mariadb',
            'MYSQL_DATABASE' => 'dev',
            'MYSQL_USER' => 'app',
            'MYSQL_PASSWORD' => null,
        ];
    }
}