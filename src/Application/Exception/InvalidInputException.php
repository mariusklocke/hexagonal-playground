<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Exception;

use Exception;
use HexagonalPlayground\Domain\ExceptionInterface;

class InvalidInputException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-INVALID-INPUT';

    public function getHttpStatusCode(): int
    {
        return 400;
    }
}
