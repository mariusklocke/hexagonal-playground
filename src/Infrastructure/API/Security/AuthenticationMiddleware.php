<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AuthenticationMiddleware implements MiddlewareInterface
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $rawHeaderValue
     * @return string[]
     */
    private function parseAuthHeader(string $rawHeaderValue): array
    {
        $parts  = explode(' ', $rawHeaderValue, 2);
        $secret = count($parts) > 1 ? $parts[1] : $parts[0];
        $type   = count($parts) > 1 ? $parts[0]: 'bearer';
        if (!is_string($type) || !is_string($secret)) {
            throw new AuthenticationException('Malformed Authorization Header');
        }

        return [$type, $secret];
    }

    /**
     * @param string $encoded
     * @return array
     */
    private function parseCredentials(string $encoded): array
    {
        $parts    = explode(':', base64_decode($encoded), 2);
        $email    = $parts[0] ?? null;
        $password = $parts[1] ?? null;
        if (!is_string($email) || !is_string($password)) {
            throw new AuthenticationException('Malformed BasicAuth credentials');
        }

        return [$email, $password];
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $rawHeaderValue = $request->getHeader('Authorization')[0] ?? null;
        if (!is_string($rawHeaderValue)) {
            return $handler->handle($request);
        }

        list($type, $secret) = $this->parseAuthHeader($rawHeaderValue);
        switch (strtolower($type)) {
            case 'basic':
                list($email, $password) = $this->parseCredentials($secret);
                $context  = $this->container->get(PasswordAuthenticator::class)->authenticate($email, $password);
                $response = $handler->handle($request->withAttribute('auth', $context));

                /**
                 * Creating the token after the controller is important when changing a user password
                 * In this case the token has to be created *AFTER* the password has been changed, because otherwise
                 * it would be considered invalid for the next request
                 *
                 * @see TokenAuthenticator::authenticate()
                 */

                $token = $this->container->get(TokenFactoryInterface::class)->create(
                    $context->getUser(),
                    new DateTimeImmutable('now + 1 year')
                );

                return $response->withHeader('X-Token', $token->encode());

            case 'bearer':
                $token   = JsonWebToken::decode($secret);
                $context = $this->container->get(TokenAuthenticator::class)->authenticate($token);

                return $handler->handle($request->withAttribute('auth', $context));
        }

        throw new AuthenticationException('Unsupported authentication type');
    }
}