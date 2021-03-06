<?php

declare (strict_types = 1);

namespace SymfonyNotes\HttpBasicAuthenticatorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Email;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Password;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Credentials;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\ChainChecker;
use SymfonyNotes\HttpBasicAuthenticatorBundle\Security\HttpBasicAuthenticator;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\PasswordChecker;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\UserLockedChecker;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\UserEnabledChecker;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\AccountExpiredChecker;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\CredentialsExpiredChecker;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\CredentialCheckerInterface;
use SymfonyNotes\HttpBasicAuthenticatorBundle\Factory\AuthenticationFailure\JsonResponseFactory;
use SymfonyNotes\HttpBasicAuthenticatorBundle\Factory\AuthenticationFailure\PlainResponseFactory;
use SymfonyNotes\HttpBasicAuthenticatorBundle\Factory\AuthenticationFailure\FailureResponseFactoryInterface;

/**
 * Class HttpBasicAuthenticatorExtension
 */
class HttpBasicAuthenticatorExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $container->setAlias('notes.authenticator_failure_response', $mergedConfig['failure_response']);
        $container->setParameter('notes_authenticator_realm_message', $mergedConfig['realm_message']);
        $container->setParameter('notes_authenticator_supports_remember_me', $mergedConfig['supports_remember_me']);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->addClassesToCompile([
            Email::class,
            Password::class,
            Credentials::class,
            ChainChecker::class,
            PasswordChecker::class,
            UserLockedChecker::class,
            UserEnabledChecker::class,
            JsonResponseFactory::class,
            PlainResponseFactory::class,
            AccountExpiredChecker::class,
            HttpBasicAuthenticator::class,
            CredentialsExpiredChecker::class,
            CredentialCheckerInterface::class,
            FailureResponseFactoryInterface::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'notes_http_basic_authenticator';
    }
}
