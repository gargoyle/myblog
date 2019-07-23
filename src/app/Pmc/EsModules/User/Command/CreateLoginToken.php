<?php

namespace Pmc\EsModules\User\Command;

use Pmc\ObjectLib\Id;

/**
 * @author Gargoyle <g@rgoyle.com>
 */
class CreateLoginToken
{

    /**
     * @var Id
     */
    private $userId;

    /**
     * @var Id
     */
    private $token;

    public function __construct(Id $userId)
    {
        $this->token = new Id();
        $this->userId = $userId;
    }

    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function getToken(): Id
    {
        return $this->token;
    }

}
