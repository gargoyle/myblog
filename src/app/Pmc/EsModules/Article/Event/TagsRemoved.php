<?php

namespace Pmc\EsModules\Article\Event;

use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;


class TagsRemoved extends AbstractDomainEvent
{

    /**
     * @var array
     */
    private $tags;

    /**
     * @var Id
     */
    private $articleId;

    public function __construct(Id $articleId, array $tags)
    {
        parent::__construct();
        $this->articleId = $articleId;
        $this->tags = $tags;
    }
    
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getArticleId(): Id
    {
        return $this->articleId;
    }
        
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['articleId'] = (string) $this->articleId;
        $data['tags'] = $this->tags;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $i = new self(
                Id::fromString($data['articleId']), 
                $data['tags']);
        $i->updateFromArray($data);
        return $i;
    }

}
