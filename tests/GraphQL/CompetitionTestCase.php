<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\GraphQL;

use DateTimeImmutable;
use HexagonalPlayground\Domain\User;

abstract class CompetitionTestCase extends TestCase
{
    protected static $teamIds = [];
    protected static $pitchIds = [];
    protected static $teamManagers = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->useAdminAuth();

        if (empty(self::$teamIds)) {
            $this->createTeams();
        }

        if (empty(self::$pitchIds)) {
            $this->createPitches();
        }
    }

    private function createTeams(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $teamId = 'Team' . $i;
            $this->client->createTeam($teamId, $teamId);
            self::$teamIds[] = $teamId;

            $manager = [
                'id' => 'TeamManager' . $i,
                'email' => 'team' . $i . '@example.com',
                'password' => '123456',
                'first_name' => 'Foo',
                'last_name' => 'Bar',
                'role' => User::ROLE_TEAM_MANAGER,
                'team_ids' => [$teamId]
            ];
            $this->client->createUser($manager);
            self::$teamManagers[$teamId] = $manager;
        }
    }

    private function createPitches(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            $id = 'Pitch' . $i;
            $this->client->createPitch($id, $id, -2.45 * $i, -1.87 * $i);
            self::$pitchIds[] = $id;
        }
    }

    protected static function createMatchDayDates(int $count): array
    {
        $result = [];
        $start  = new \DateTime('2019-11-09');
        $end    = new \DateTime('2019-11-10');
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'from' => $start->format('Y-m-d'),
                'to'   => $end->format('Y-m-d')
            ];
            $start->modify('+7 days');
            $end->modify('+7 days');
        }

        return $result;
    }

    protected static function createMatchAppointments(): array
    {
        $appointments = [];
        $saturday = new DateTimeImmutable('2019-11-09');
        $sunday = new DateTimeImmutable('2019-11-10');

        $appointments[] = [
            'kickoff' => $saturday->setTime(15, 30)->format(DATE_ATOM),
            'unavailable_team_ids' => [],
            'pitch_id' => self::$pitchIds[0]
        ];

        $appointments[] = [
            'kickoff' => $saturday->setTime(17, 30)->format(DATE_ATOM),
            'unavailable_team_ids' => [self::$teamIds[0], self::$teamIds[1]],
            'pitch_id' => self::$pitchIds[1]
        ];

        $appointments[] = [
            'kickoff' => $sunday->setTime(12, 00)->format(DATE_ATOM),
            'unavailable_team_ids' => [self::$teamIds[2]],
            'pitch_id' => self::$pitchIds[0]
        ];

        $appointments[] = [
            'kickoff' => $sunday->setTime(14, 00)->format(DATE_ATOM),
            'unavailable_team_ids' => [],
            'pitch_id' => self::$pitchIds[1]
        ];

        return $appointments;
    }

    protected function useTeamManagerAuth(string $teamId)
    {
        $user = self::$teamManagers[$teamId];
        $this->client->useCredentials($user['email'], $user['password']);
    }
}