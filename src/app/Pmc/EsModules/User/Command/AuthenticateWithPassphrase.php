<?php

namespace Pmc\EsModules\User\Command;

use Pmc\EsModules\User\ValueObject\Passphrase;
use Pmc\ObjectLib\Id;

/**
 * Authenticate the user with a password.
 *
 * @author Gargoyle <g@rgoyle.com>
 */
class AuthenticateWithPassphrase
{

    /**
     * @var Id
     */
    private $userId;

    /**
     * @var Passphrase
     */
    private $passphrase;

    public function __construct(Id $userId, Passphrase $passphrase)
    {
        $this->passphrase = $passphrase;
        $this->userId = $userId;
    }

    public function getPassphrase(): Passphrase
    {
        return $this->passphrase;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

}
