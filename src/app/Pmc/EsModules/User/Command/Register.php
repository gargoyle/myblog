<?php

namespace Pmc\EsModules\User\Command;

use Pmc\EsModules\User\ValueObject\EmailAddress;
use Pmc\EsModules\User\ValueObject\FullName;
use Pmc\EsModules\User\ValueObject\Passphrase;
use Pmc\EsModules\User\ValueObject\Password;
use Pmc\EsModules\User\ValueObject\Username;
use Pmc\ObjectLib\Id;

/**
 * @author Gargoyle <g@rgoyle.com>
 */
class Register
{

    /**
     * @var array
     */
    private $roles;

    /**
     *
     * @var Id
     */
    private $id;

    /**
     * @var Username
     */
    private $username;

    /**
     * @var FullName
     */
    private $fullName;

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var Password
     */
    private $passphrase;

    public function __construct(
            Passphrase $passphrase, 
            EmailAddress $emailAddress, 
            FullName $fullName, 
            Username $username,
            array $roles
    )
    {
        $this->id = new Id();
        $this->passphrase = $passphrase;
        $this->emailAddress = $emailAddress;
        $this->fullName = $fullName;
        $this->username = $username;
        $this->roles = $roles;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getUsername(): Username
    {
        return $this->username;
    }

    public function getEmailAddress(): EmailAddress
    {
        return $this->emailAddress;
    }

    public function getPassphrase(): Passphrase
    {
        return $this->passphrase;
    }

    public function getFullName(): FullName
    {
        return $this->fullName;
    }
    
    public function getRoles(): array
    {
        return $this->roles;
    }

}
