<?php

namespace Pmc\EsModules\User\Aggregate;

use DomainException;
use Pmc\EsModules\User\Command\CreateLoginToken;
use Pmc\EsModules\User\Event\AuthenticationSucceeded;
use Pmc\EsModules\User\Event\LoginTokenCreated;
use Pmc\EsModules\User\Event\UserRegistered;
use Pmc\EsModules\User\Exception\AuthenticationFailed;
use Pmc\EsModules\User\ValueObject\EmailAddress;
use Pmc\EsModules\User\ValueObject\FullName;
use Pmc\EsModules\User\ValueObject\Passphrase;
use Pmc\EsModules\User\ValueObject\Username;
use Pmc\EventSourceLib\Aggregate\AbstractRoot;
use Pmc\EventSourceLib\Event\EventStream;
use Pmc\ObjectLib\Id;



/**
 * @author Gargoyle <g@rgoyle.com>
 */
class User extends AbstractRoot
{
    private $userId;
    private $username;
    private $emailAddress;
    private $passwordHash;
    private $fullName;
    private $loginTokenLastCreated;
    private $loginToken;
    private $roles;

    
    public static function register(
            Id $userId,
            Passphrase $passphrase, 
            EmailAddress $emailAddress, 
            FullName $fullName, 
            Username $username,
            array $roles = []): EventStream
    {
        $instance = new self(new EventStream($userId));
        $instance->raise(new UserRegistered(
                $userId,
                $username,
                $emailAddress,
                $passphrase->getHash(),
                $fullName,
                $roles));
        return $instance->pendingEvents;
    }
    
    public function authenticate(Passphrase $passToCheck): EventStream
    {
        if (!$passToCheck->verify($this->passwordHash)) {
            throw new AuthenticationFailed("Password missmatch");
        }
        $this->raise(new AuthenticationSucceeded($this->userId));
        return $this->pendingEvents;
    }
    
    public function authenticateWithToken(Id $tokenToCheck): EventStream
    {
        if (!$this->loginToken instanceof Id) {
            throw new AuthenticationFailed("No token");
        }
        if (!$tokenToCheck->equals($this->loginToken)) {
            throw new AuthenticationFailed("Invalid token");
        }
        $this->raise(new AuthenticationSucceeded($this->userId));
        return $this->pendingEvents;
    }
    
    public function createLoginToken(CreateLoginToken $command)
    {
        $fifteenMinsAgo = (time() - 60 * 15);
        $diff = $this->loginTokenLastCreated - $fifteenMinsAgo;
        if (($diff > 0)) {
            throw new DomainException(sprintf(
                    "You cannot create another login token yet. Please wait %s mins and try again",
                    (int)($diff / 60)));
        }

        $this->raise(new LoginTokenCreated($this->userId, $command->getToken()));
        return $this->pendingEvents;
    }
    
    protected function applyUserRegisteredEvent(UserRegistered $event)
    {
        $this->userId = $event->getUserId();
        $this->username = $event->getUsername();
        $this->emailAddress = $event->getEmailAddress();
        $this->passwordHash = $event->getPasswordHash();
        $this->fullName = $event->getFullName();
        $this->roles = $event->getRoles();
    }
    
    protected function applyAuthenticationSucceededEvent(AuthenticationSucceeded $event)
    {
        $this->loginToken = null;
    }
    
    protected function applyLoginTokenCreatedEvent(LoginTokenCreated $event)
    {
        $this->loginToken = $event->getToken();
        $this->loginTokenLastCreated = $event->getTimestamp();
    }
    
}
