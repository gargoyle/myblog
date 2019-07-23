<?php

namespace Pmc\EsModules\User\Event;

use Pmc\EsModules\User\ValueObject\EmailAddress;
use Pmc\EsModules\User\ValueObject\FullName;
use Pmc\EsModules\User\ValueObject\Username;
use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

/**
 * @author Gargoyle <g@rgoyle.com>
 */
class UserRegistered extends AbstractDomainEvent
{

    /**
     * @var array
     */
    private $roles;

    /**
     * @var Id
     */
    private $userId;

    /**
     * @var FullName
     */
    private $fullName;

    /**
     * @var string
     */
    private $passwordHash;

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var Username
     */
    private $username;

    public function __construct(
            Id $userId, 
            Username $username, 
            EmailAddress $emailAddress, 
            string $passwordHash, 
            FullName $fullName,
            array $roles = [])
    {
        parent::__construct();

        $this->username = $username;
        $this->emailAddress = $emailAddress;
        $this->passwordHash = $passwordHash;
        $this->fullName = $fullName;
        $this->userId = $userId;
        $this->roles = $roles;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getEmailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }

    public function getFullName(): FullName
    {
        return $this->fullName;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['userId'] = (string) $this->userId;
        $data['username'] = (string) $this->username;
        $data['emailAddress'] = (string) $this->emailAddress;
        $data['passwordHash'] = (string) $this->passwordHash;
        $data['fullName'] = (string) $this->fullName;
        $data['roles'] = $this->roles;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $instance = new self(
                Id::fromString($data['userId']),
                new Username($data['username']),
                new EmailAddress($data['emailAddress']),
                (string) $data['passwordHash'],
                new FullName($data['fullName']),
                $data['roles']
                );
        $instance->updateFromArray($data);
        return $instance;
    }

}
