<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\StringType;
use HexagonalPlayground\Application\InputParser;

class DateType extends StringType
{
    use SingletonTrait;

    public $name = 'Date';

    /**
     * @param mixed $value
     * @return \DateTimeImmutable|null
     */
    public function parseValue($value)
    {
        return $value !== null ? InputParser::parseDate(parent::parseValue($value)) : null;
    }

    /**
     * @param \GraphQL\Language\AST\Node $valueNode
     * @param array|null $variables
     * @return \DateTimeImmutable|null
     */
    public function parseLiteral($valueNode, ?array $variables = null)
    {
        return $this->parseValue($this->parseLiteral($valueNode, $variables));
    }
}