<?php

namespace Pmc\EsModules\User\Command;

use Pmc\ObjectLib\Id;

/**
 * Authenticate the user with a password.
 *
 * @author Gargoyle <g@rgoyle.com>
 */
class AuthenticateWithToken
{
    /**
     * @var Id
     */
    private $userId;

    /**
     * @var Id
     */
    private $token;

    public function __construct(Id $userId, Id $token)
    {
        $this->token = $token;
        $this->userId = $userId;
    }

    public function getToken() : Id
    {
        return $this->token;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }
}
