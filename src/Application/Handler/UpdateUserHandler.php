<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\UpdateUserCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class UpdateUserHandler implements AuthAwareHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(UserRepositoryInterface $userRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->userRepository = $userRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param UpdateUserCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(UpdateUserCommand $command, AuthContext $authContext): void
    {
        /** @var User $user */
        $user = $this->userRepository->find($command->getUserId());
        $isAdmin = new IsAdmin($authContext->getUser());

        // Changing other users than oneself requires admin rights
        if (!$user->equals($authContext->getUser())) {
            $isAdmin->check();
        }

        // Changing user role requires admin rights
        if (null !== $command->getRole() && !$user->hasRole($command->getRole())) {
            $isAdmin->check();
            $user->setRole($command->getRole());
        }

        if (null !== $command->getEmail() && $command->getEmail() !== $user->getEmail()) {
            $this->userRepository->assertEmailDoesNotExist($command->getEmail());
            $user->setEmail($command->getEmail());
        }

        if (null !== $command->getFirstName()) {
            $user->setFirstName($command->getFirstName());
        }

        if (null !== $command->getLastName()) {
            $user->setLastName($command->getLastName());
        }

        if (null !== $command->getTeamIds()) {
            $isAdmin->check();

            $user->clearTeams();
            foreach ($command->getTeamIds() as $teamId) {
                /** @var Team $team */
                $team = $this->teamRepository->find($teamId);
                $user->addTeam($team);
            }
        }

        $this->userRepository->save($user);
    }
}