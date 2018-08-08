<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Util\Assert;

class MatchResult
{
    /** @var int */
    private $homeScore;

    /** @var int */
    private $guestScore;

    public function __construct(int $homeScore, int $guestScore)
    {
        $this->assertValidScoreValue($homeScore);
        $this->assertValidScoreValue($guestScore);
        $this->homeScore  = $homeScore;
        $this->guestScore = $guestScore;
    }

    /**
     * @return int
     */
    public function getHomeScore(): int
    {
        return $this->homeScore;
    }

    /**
     * @return int
     */
    public function getGuestScore(): int
    {
        return $this->guestScore;
    }

    /**
     * @param int $value
     * @throws DomainException
     */
    private function assertValidScoreValue(int $value)
    {
        Assert::greaterOrEqualThan($value, 0, 'Match scores have to be greater or equal than 0');
        Assert::lessOrEqualThan($value, 99, 'Match scores have to be less or equal than 99');
    }
}
