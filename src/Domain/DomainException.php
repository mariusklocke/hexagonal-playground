<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Exception;

class DomainException extends Exception implements ExceptionInterface
{
    /** @var string */
    protected $code = 'ERR-DOMAIN';

    /**
     * Returns the appropriate HTTP response status code
     *
     * @return int
     */
    public function getHttpStatusCode(): int
    {
        return 400;
    }
}
