<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;

class CommandBus
{
    /** @var HandlerResolver */
    private $resolver;

    /** @var OrmTransactionWrapperInterface */
    private $transactionWrapper;

    /**
     * @param HandlerResolver $resolver
     * @param OrmTransactionWrapperInterface $transactionWrapper
     */
    public function __construct(HandlerResolver $resolver, OrmTransactionWrapperInterface $transactionWrapper)
    {
        $this->resolver = $resolver;
        $this->transactionWrapper = $transactionWrapper;
    }

    /**
     * @param CommandInterface $command
     */
    public function execute(CommandInterface $command): void
    {
        $handler = $this->resolver->resolve($command);
        $this->transactionWrapper->transactional(function() use ($handler, $command) {
            $handler($command);
        });
    }
}
