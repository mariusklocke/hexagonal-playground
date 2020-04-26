<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\ServiceConfiguratorInterface;
use HexagonalPlayground\Infrastructure\Environment;
use Psr\Container\ContainerInterface;
use RuntimeException;

class JsonWebTokenConfigurator implements ServiceConfiguratorInterface
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
        return 'JWT';
    }

    public function getDefaults(): array
    {
        return [
            'JWT_SECRET' => JsonWebToken::generateSecret()
        ];
    }

    public function validate(): void
    {
        $secret = Environment::get('JWT_SECRET');
        if (null === $secret || strlen($secret) === 0) {
            throw new RuntimeException('Invalid JWT Secret');
        }
    }
}