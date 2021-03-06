<?php

declare (strict_types = 1);

namespace SymfonyNotes\HttpBasicAuthenticatorBundle\Tests\CredentialChecker;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Email;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Password;
use SymfonyNotes\HttpBasicAuthenticatorBundle\ValueObject\Credentials;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyNotes\HttpBasicAuthenticatorBundle\CredentialChecker\PasswordChecker;

/**
 * Class PasswordCheckerTest
 */
class PasswordCheckerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PasswordChecker
     */
    private $checker;

    /**
     * @var UserPasswordEncoderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $passwordEncoder;

    /**
     * @var AdvancedUserInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $user;

    /**
     * @var Credentials
     */
    private $credentials;

    /**
     * Check positive scenarios.
     */
    public function testPositive()
    {
        $this->passwordEncoder->expects(self::once())
            ->method('isPasswordValid')
            ->willReturn(true);

        $this->checker->check($this->user, $this->credentials);
    }

    /**
     * Check exception throwing.
     */
    public function testNegative()
    {
        self::expectException(AuthenticationException::class);
        self::expectExceptionMessage('Wrong password.');

        $this->passwordEncoder->expects(self::once())
            ->method('isPasswordValid')
            ->willReturn(false);

        $this->checker->check($this->user, $this->credentials);
    }

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->user = $this->createMock(AdvancedUserInterface::class);
        $this->credentials = new Credentials(new Email('test@test.com'), new Password('test'));
        $this->passwordEncoder = $this->createMock(UserPasswordEncoderInterface::class);
        $this->checker = new PasswordChecker($this->passwordEncoder);
    }
}
