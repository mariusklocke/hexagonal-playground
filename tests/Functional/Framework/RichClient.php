<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional\Framework;

use Psr\Http\Message\ResponseInterface;
use stdClass;

class RichClient
{
    /** @var HttpClient */
    private $httpClient;

    /** @var array */
    private $headers;

    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
        $this->headers    = [];
    }

    public function setBasicAuth(string $username, string $password): void
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
    }

    public function clearAuth(): void
    {
        unset($this->headers['Authorization']);
    }

    public function createSeason(string $name): stdClass
    {
        return $this->decodeBody($this->httpClient->post('/api/seasons', ['name' => $name], $this->headers));
    }

    public function getAllSeasons(): array
    {
        return $this->decodeBody($this->httpClient->get('/api/seasons'));
    }

    public function getSeason(string $id): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/seasons/' . $id));
    }

    public function getTeamsInSeason(string $seasonId): array
    {
        return $this->decodeBody($this->httpClient->get('/api/seasons/' . $seasonId . '/teams'));
    }

    public function deleteSeason(string $id): void
    {
        $this->handleErrors($this->httpClient->delete('/api/seasons/' . $id, $this->headers));
    }

    public function startSeason(string $id): void
    {
        $this->handleErrors($this->httpClient->post('/api/seasons/' . $id . '/start', [], $this->headers));
    }

    public function addTeamToSeason(string $seasonId, string $teamId): void
    {
        $this->handleErrors($this->httpClient->put('/api/seasons/' . $seasonId . '/teams/' . $teamId, [], $this->headers));
    }

    public function removeTeamFromSeason(string $seasonId, string $teamId): void
    {
        $this->handleErrors($this->httpClient->delete('/api/seasons/' . $seasonId . '/teams/' . $teamId));
    }

    public function getSeasonRanking(string $seasonId): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/seasons/' . $seasonId . '/ranking'));
    }

    public function createTeam(string $name): stdClass
    {
        return $this->decodeBody($this->httpClient->post('/api/teams', ['name' => $name], $this->headers));
    }

    public function createMatches(string $seasonId, array $matchDays): void
    {
        $this->handleErrors($this->httpClient->post(
            '/api/seasons/' . $seasonId . '/match_days',
            ['match_days' => $matchDays],
            $this->headers
        ));
    }

    public function getMatch(string $matchId): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/matches/' . $matchId));
    }

    public function getMatchesByMatchDayId(string $matchDayId): array
    {
        return $this->decodeBody($this->httpClient->get(
            '/api/matches?' . http_build_query(['match_day_id' => $matchDayId])
        ));
    }

    public function getMatchesBySeasonId(string $seasonId): array
    {
        return $this->decodeBody($this->httpClient->get(
            '/api/matches?' . http_build_query(['season_id' => $seasonId])
        ));
    }

    public function submitMatchResult(string $matchId, int $homeScore, int $guestScore): void
    {
        $this->handleErrors($this->httpClient->post(
            '/api/matches/' . $matchId . '/result',
            ['home_score' => $homeScore, 'guest_score' => $guestScore],
            $this->headers
        ));
    }

    public function createTournament(string $name): stdClass
    {
        return $this->decodeBody($this->httpClient->post('/api/tournaments', ['name' => $name], $this->headers));
    }

    public function getMatchesInTournament(string $tournamentId): array
    {
        return $this->decodeBody($this->httpClient->get(
            '/api/matches?' . http_build_query(['tournament_id' => $tournamentId])
        ));
    }

    public function getAllTournaments(): array
    {
        return $this->decodeBody($this->httpClient->get('/api/tournaments'));
    }

    public function getTournament(string $id): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/tournaments/' . $id));
    }

    public function setTournamentRound(string $tournamentId, int $round, array $teamPairs, array $datePeriod): void
    {
        $this->handleErrors($this->httpClient->put(
            '/api/tournaments/' . $tournamentId . '/rounds/' . $round,
            [
                'date_period' => $datePeriod,
                'team_pairs'  => $teamPairs
            ],
            $this->headers
        ));
    }

    public function getAuthenticatedUser(): stdClass
    {
        return $this->decodeBody($this->httpClient->get('/api/users/me', $this->headers));
    }

    public function changePassword(string $newPassword): void
    {
        $this->handleErrors($this->httpClient->put(
            '/api/users/me/password',
            ['new_password' => $newPassword],
            $this->headers
        ));
    }

    public function createUser(array $properties): stdClass
    {
        return $this->decodeBody($this->httpClient->post('/api/users', $properties, $this->headers));
    }

    public function createPitch($label, $latitude, $longitude): stdClass
    {
        return $this->decodeBody($this->httpClient->post(
            '/api/pitches',
            [
                'label' => $label,
                'location_latitude' => $latitude,
                'location_longitude' => $longitude
            ],
            $this->headers
        ));
    }

    public function locateMatch(string $matchId, string $pitchId)
    {
        $this->handleErrors($this->httpClient->post(
            '/api/matches/' . $matchId . '/location',
            [
                'pitch_id' => $pitchId
            ],
            $this->headers
        ));
    }

    public function scheduleMatch(string $matchId, string $kickoffDate)
    {
        $this->handleErrors($this->httpClient->post(
            '/api/matches/' . $matchId . '/kickoff',
            [
                'kickoff' => $kickoffDate
            ],
            $this->headers
        ));
    }

    public function updateTeamContact(string $teamId, array $contact)
    {
        $this->handleErrors($this->httpClient->put(
            '/api/teams/' . $teamId . '/contact',
            $contact,
            $this->headers
        ));
    }

    public function updatePitchContact(string $pitchId, array $contact)
    {
        $this->handleErrors($this->httpClient->put(
            '/api/pitches/' . $pitchId . '/contact',
            $contact,
            $this->headers
        ));
    }

    public function getTeam(string $teamId)
    {
        return $this->decodeBody($this->httpClient->get(
            '/api/teams/' . $teamId
        ));
    }

    public function getPitch(string $pitchId)
    {
        return $this->decodeBody($this->httpClient->get(
            '/api/pitches/' . $pitchId
        ));
    }

    private function decodeBody(ResponseInterface $response)
    {
        $this->handleErrors($response);
        return $this->httpClient->parseBody($response->getBody());
    }

    private function handleErrors(ResponseInterface $response): void
    {
        if ($response->getStatusCode() >= 400) {
            $body    = $this->httpClient->parseBody($response->getBody());
            $message = isset($body->message) ? $body->message : $response->getReasonPhrase();
            throw new ApiException($message, $response->getStatusCode());
        }
    }
}