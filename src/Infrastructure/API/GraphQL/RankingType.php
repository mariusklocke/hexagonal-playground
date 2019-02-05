<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;

class RankingType extends ObjectType
{
    use SingletonTrait;

    public function __construct()
    {
        $config = [
            'fields' => function() {
                return [
                    'updated_at' => [
                        'type' => Type::string()
                    ],
                    'positions' => [
                        'type' => Type::listOf(RankingPositionType::getInstance())
                    ],
                    'penalties' => [
                        'type' => Type::listOf(RankingPenaltyType::getInstance())
                    ],
                    'season' => [
                        'type' => SeasonType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            /** @var SeasonRepository $repo */
                            $repo = $context->getContainer()->get(SeasonRepository::class);

                            return $repo->findSeasonById($root['season_id']);
                        }
                    ],
                ];
            }
        ];
        parent::__construct($config);
    }
}