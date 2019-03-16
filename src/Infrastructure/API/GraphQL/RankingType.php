<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class RankingType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'id' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'updated_at' => [
                        'type' => Type::nonNull(Type::string())
                    ],
                    'positions' => [
                        'type' => Type::listOf(RankingPositionType::getInstance())
                    ],
                    'penalties' => [
                        'type' => Type::listOf(RankingPenaltyType::getInstance())
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }
}