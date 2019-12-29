<?php

namespace Pmc\EsModules\Article\Event;

use Pmc\EsModules\Article\ValueObject\Body;
use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

class BodyChanged extends AbstractDomainEvent
{

    /**
     * @var Body
     */
    private $newBody;

    /**
     * @var Id
     */
    private $articleId;

    public function __construct(Id $articleId, Body $newBody)
    {
        parent::__construct();

        $this->articleId = $articleId;
        $this->newBody = $newBody;
    }

    public function getBody(): Body
    {
        return $this->newBody;
    }

    public function getArticleId(): Id
    {
        return $this->articleId;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['articleId'] = (string) $this->articleId;
        $data['newBody'] = (string) $this->newBody;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $i = new self(
                Id::fromString($data['articleId']),
                new Body($data['newBody']));
        $i->updateFromArray($data);
        return $i;
    }

}
