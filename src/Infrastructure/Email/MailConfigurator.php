<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Email;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\ServiceConfiguratorInterface;
use Psr\Container\ContainerInterface;

class MailConfigurator implements ServiceConfiguratorInterface
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
        return 'mail';
    }

    public function getDefaults(): array
    {
        return [
            'EMAIL_SENDER_ADDRESS' => 'noreply@example.com',
            'EMAIL_SENDER_NAME' => 'Wilde Liga Bremen',
            'EMAIL_URL' => 'smtp://maildev:25'
        ];
    }

    public function validate(): void
    {
        $this->container->get(MailerInterface::class);
    }
}