<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Permission\CanManageTeam;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Value\ContactPerson;

class UpdateTeamContactHandler implements AuthAwareHandler
{
    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param UpdateTeamContactCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(UpdateTeamContactCommand $command, AuthContext $authContext): void
    {
        /** @var Team $team */
        $team = $this->teamRepository->find($command->getTeamId());
        $canManageTeam = new CanManageTeam($team, $authContext->getUser());
        $canManageTeam->check();
        $contact = new ContactPerson(
            $command->getFirstName(),
            $command->getLastName(),
            $command->getPhone(),
            $command->getEmail()
        );
        $team->setContact($contact);
    }
}