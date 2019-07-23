<?php

namespace Pmc\EsModules\User\Event;

use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

/**
 * @author Paul Court <emails@paulcourt.co.uk>
 */
class AuthenticationSucceeded extends AbstractDomainEvent
{

    /**
     * @var Id
     */
    private $userId;

    public function __construct(Id $userId)
    {
        parent::__construct();
        $this->userId = $userId;
    }
    
    public function getUserId(): Id
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['userId'] = (string) $this->userId;
        return $data;
    }

    public static function fromArray(array $data): self
    {
        $instance = new self(Id::fromString($data['userId']));
        $instance->updateFromArray($data);
        return $instance;
    }

}
