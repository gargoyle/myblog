<?php

namespace Pmc\EsModules\User\Event;

use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

/**
 * @author Gargoyle <g@rgoyle.com>
 */
class LoginTokenCreated extends AbstractDomainEvent
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
        parent::__construct();
        $this->token = $token;
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

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['userId'] = (string) $this->userId;
        $data['token'] = (string) $this->token;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $instance = new self(
                Id::fromString($data['userId']),
                Id::fromString($data['token']));
        $instance->updateFromArray($data);
        return $instance;
    }

}
