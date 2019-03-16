<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Deferred;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedPitchLoader;
use HexagonalPlayground\Infrastructure\API\GraphQL\Loader\BufferedTeamLoader;

class MatchType extends ObjectType
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
                    'home_team' => [
                        'type' => Type::nonNull(TeamType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            return $this->resolveTeam($root['home_team_id'], $context);
                        }
                    ],
                    'guest_team' => [
                        'type' => Type::nonNull(TeamType::getInstance()),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            return $this->resolveTeam($root['guest_team_id'], $context);
                        }
                    ],
                    'kickoff' => [
                        'type' => Type::string()
                    ],
                    'home_score' => [
                        'type' => Type::int()
                    ],
                    'guest_score' => [
                        'type' => Type::int()
                    ],
                    'cancelled_at' => [
                        'type' => Type::string()
                    ],
                    'cancellation_reason' => [
                        'type' => Type::string()
                    ],
                    'pitch' => [
                        'type' => PitchType::getInstance(),
                        'resolve' => function (array $root, $args, AppContext $context) {
                            if (null === $root['pitch_id']) {
                                return null;
                            }

                            /** @var BufferedPitchLoader $loader */
                            $loader = $context->getContainer()->get(BufferedPitchLoader::class);
                            $loader->addPitch($root['pitch_id']);
                            return new Deferred(function () use ($loader, $root) {
                                return $loader->getByPitch($root['pitch_id']);
                            });
                        }
                    ]
                ];
            }
        ];
        parent::__construct($config);
    }

    private function resolveTeam(string $teamId, AppContext $context): Deferred
    {
        /** @var BufferedTeamLoader $loader */
        $loader = $context->getContainer()->get(BufferedTeamLoader::class);
        $loader->addTeam($teamId);
        return new Deferred(function() use ($loader, $teamId) {
            return $loader->getByTeam($teamId);
        });
    }
}