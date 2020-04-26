<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DI;
use HexagonalPlayground\Application\ServiceConfiguratorInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            TokenFactoryInterface::class => DI\get(JsonWebTokenFactory::class),
            ServiceConfiguratorInterface::class => DI\add(DI\get(JsonWebTokenConfigurator::class))
        ];
    }
}