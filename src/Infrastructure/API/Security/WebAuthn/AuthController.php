<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use DateTimeImmutable;
use Exception;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\StatusCode;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialSourceRepository;

class AuthController
{
    use ResponseFactoryTrait;

    /** @var PublicKeyCredentialSourceRepository */
    private $credentialRepository;

    /** @var RequestOptionsFactory */
    private $requestOptionsFactory;

    /** @var OptionsStoreInterface */
    private $optionsStore;

    /** @var PublicKeyCredentialLoader */
    private $credentialLoader;

    /** @var AuthenticatorAssertionResponseValidator */
    private $authenticatorAssertionResponseValidator;

    /** @var FakeCredentialDescriptorFactory */
    private $fakeCredentialDescriptorFactory;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var TokenFactoryInterface */
    private $tokenFactory;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param RequestOptionsFactory $requestOptionsFactory
     * @param OptionsStoreInterface $optionsStore
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator
     * @param FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory
     * @param UserRepositoryInterface $userRepository
     * @param TokenFactoryInterface $tokenFactory
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, RequestOptionsFactory $requestOptionsFactory, OptionsStoreInterface $optionsStore, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator, FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory, UserRepositoryInterface $userRepository, TokenFactoryInterface $tokenFactory)
    {
        $this->credentialRepository = $credentialRepository;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->optionsStore = $optionsStore;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAssertionResponseValidator = $authenticatorAssertionResponseValidator;
        $this->fakeCredentialDescriptorFactory = $fakeCredentialDescriptorFactory;
        $this->userRepository = $userRepository;
        $this->tokenFactory = $tokenFactory;
    }


    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function options(Request $request): ResponseInterface
    {
        $email = $request->getParsedBodyParam('email');
        TypeAssert::assertString($email, 'email');

        $options = $this->requestOptionsFactory->create(
            $request->getUri()->getHost(),
            $this->getCredentialDescriptors($email)
        );

        $this->optionsStore->save($email, $options);

        return $this->createResponse(StatusCode::HTTP_OK, $options);
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function login(Request $request): ResponseInterface
    {
        $email = $request->getParsedBodyParam('email');
        TypeAssert::assertString($email, 'email');

        try {
            $credential = $this->credentialLoader->loadArray($request->getParsedBody());
            $authenticatorResponse = $credential->getResponse();
        } catch (Exception $e) {
            throw new InvalidInputException($e->getMessage());
        }

        if (!$authenticatorResponse instanceof AuthenticatorAssertionResponse) {
            throw new InvalidInputException('Response from authenticator is not an AuthenticatorAssertionResponse');
        }

        $options = $this->optionsStore->get($email);
        if (!$options instanceof PublicKeyCredentialRequestOptions) {
            throw new InvalidInputException('Cannot find request options for requested user');
        }

        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (NotFoundException $e) {
            throw new AuthenticationException('Authentication failed');
        }

        try {
            $this->authenticatorAssertionResponseValidator->check(
                $credential->getRawId(),
                $authenticatorResponse,
                $options,
                $request,
                $user->getId()
            );
        } catch (Exception $e) {
            throw new AuthenticationException('Authentication failed');
        }

        $token = $this->tokenFactory->create($user, new DateTimeImmutable('now + 1 year'));

        return $this
            ->createResponse(StatusCode::HTTP_OK, $user->getPublicProperties())
            ->withHeader('X-Token', $token->encode());
    }

    /**
     * @param string $email
     * @return PublicKeyCredentialDescriptor[]
     */
    private function getCredentialDescriptors(string $email): array
    {
        $credentialDescriptors = [];

        try {
            $user = $this->userRepository->findByEmail($email);
            $credentials = $this->credentialRepository->findAllForUserEntity(UserConverter::convert($user));
            foreach ($credentials as $credentialSource) {
                $credentialDescriptors[] = $credentialSource->getPublicKeyCredentialDescriptor();
            }
        } catch (NotFoundException $e) {
            $credentialDescriptors[] = $this->fakeCredentialDescriptorFactory->create($email);
        }

        return $credentialDescriptors;
    }
}