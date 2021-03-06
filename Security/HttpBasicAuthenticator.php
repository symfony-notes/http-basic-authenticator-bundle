<?php

declare (strict_types = 1);

namespace SymfonyNotes\HttpBasicAuthenticatorBundle\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Email;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Password;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Credentials;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\CredentialCheckerInterface;
use SymfonyNotes\HttpBasicAuthenticatorBundle\Factory\AuthenticationFailure\FailureResponseFactoryInterface;

/**
 * Class HttpBasicAuthenticator
 */
class HttpBasicAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var CredentialCheckerInterface
     */
    private $credentialChecker;

    /**
     * @var FailureResponseFactoryInterface
     */
    private $failureResponseFactory;

    /**
     * @var string
     */
    private $realmMessage;

    /**
     * @var bool
     */
    private $isSupportsRememberMe;

    /**
     * @param CredentialCheckerInterface      $credentialChecker
     * @param FailureResponseFactoryInterface $failureResponseFactory
     * @param bool                            $isSupportsRememberMe
     * @param string                          $realmMessage
     */
    public function __construct(
        CredentialCheckerInterface $credentialChecker,
        FailureResponseFactoryInterface $failureResponseFactory,
        bool $isSupportsRememberMe,
        string $realmMessage
    ) {
        $this->credentialChecker = $credentialChecker;
        $this->failureResponseFactory = $failureResponseFactory;
        $this->isSupportsRememberMe = $isSupportsRememberMe;
        $this->realmMessage = $realmMessage;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('', Response::HTTP_UNAUTHORIZED, [
            'WWW-Authenticate' => sprintf('Basic realm="%s"', $this->realmMessage),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        $email = $request->headers->get('PHP_AUTH_USER');
        $password = $request->headers->get('PHP_AUTH_PW');

        if (!$email || !$password) {
            return null; // Return null and no other methods will be called
        }

        return new Credentials(new Email($email), new Password($password));
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername((string) $credentials->getEmail());
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $this->credentialChecker->check($user, $credentials);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->failureResponseFactory->createFromException($exception);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null; // on success, let the request continue
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return $this->isSupportsRememberMe;
    }
}
