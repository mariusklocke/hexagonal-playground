<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\ServiceConfiguratorInterface;
use Psr\Container\ContainerInterface;
use Redis;

class RedisConfigurator implements ServiceConfiguratorInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName(): string
    {
        return 'redis';
    }

    public function getDefaults(): array
    {
        return [
            'REDIS_HOST' => 'redis'
        ];
    }

    public function validate(): void
    {
        $this->container->get(Redis::class);
    }
}