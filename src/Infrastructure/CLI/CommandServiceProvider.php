<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Import\Importer;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\CommandLoader\FactoryCommandLoader;

class CommandServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container[CommandLoaderInterface::class] = function () use ($container) {
            return new FactoryCommandLoader([
                'app:load-fixtures' => function () use ($container) {
                    return new LoadFixturesCommand($container['commandBus']);
                },
                'app:create-user' => function () use ($container) {
                    return new CreateUserCommand($container['commandBus']);
                },
                'app:import-season' => function () use ($container) {
                    return new L98ImportCommand(
                        $container[OrmTransactionWrapperInterface::class],
                        $container[Importer::class]
                    );
                },
                'app:generate-jwt-secret' => function () use ($container) {
                    return new GenerateJwtSecretCommand();
                }
            ]);
        };
    }
}