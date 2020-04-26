<?php declare(strict_types=1);

namespace HexagonalPlayground\Application;

use Exception;

interface ServiceConfiguratorInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return array
     */
    public function getDefaults(): array;

    /**
     * @throws Exception in case configuration is invalid
     */
    public function validate(): void;
}