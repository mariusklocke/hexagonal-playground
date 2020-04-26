<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\ServiceConfiguratorInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerConfigurator implements ServiceConfiguratorInterface
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
        return 'logger';
    }

    public function getDefaults(): array
    {
        return [
            'LOG_LEVEL' => LogLevel::NOTICE
        ];
    }

    public function validate(): void
    {
        $this->container->get(LoggerInterface::class);
    }
}