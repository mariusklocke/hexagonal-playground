<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Infrastructure\API\HttpException;
use Slim\Http\Request;
use Slim\Http\Response;

class TournamentCommandController extends CommandController
{
    use DateParser;

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request) : Response
    {
        $name = $request->getParsedBodyParam('name');
        $this->assertString('name', $name);
        $id   = $this->commandBus->execute(new CreateTournamentCommand($name));
        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param string $tournamentId
     * @param int $round
     * @param Request $request
     * @return Response
     */
    public function setRound(string $tournamentId, int $round, Request $request)
    {
        $teamIdPairs = $request->getParsedBodyParam('team_pairs');
        $plannedFor  = $request->getParsedBodyParam('planned_for');
        if (null !== $plannedFor) {
            $plannedFor = $this->parseDate($plannedFor);
        }
        $this->assertArray('team_pairs', $teamIdPairs);

        if (empty($teamIdPairs)) {
            throw HttpException::createBadRequest('Team pairs cannot be empty');
        }

        if (count($teamIdPairs) > 64) {
            throw HttpException::createBadRequest('Request exceeds maximum amount of 64 team pairs.');
        }

        $command = new SetTournamentRoundCommand($tournamentId, $round, $teamIdPairs, $plannedFor);
        $this->commandBus->execute($command);
        return new Response(204);
    }
}