<?php

namespace Pmc\EsModules\Article\Event;

use Pmc\EsModules\Article\ValueObject\Slug;
use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

class SlugChanged extends AbstractDomainEvent
{

    /**
     * @var Slug
     */
    private $newSlug;

    /**
     * @var Id
     */
    private $articleId;

    public function __construct(Id $articleId, Slug $newSlug)
    {
        parent::__construct();
        
        $this->articleId = $articleId;
        $this->newSlug = $newSlug;
    }
    
    public function getNewSlug(): Slug
    {
        return $this->newSlug;
    }

    public function getArticleId(): Id
    {
        return $this->articleId;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['articleId'] = (string) $this->articleId;
        $data['newSlug'] = (string) $this->newSlug;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $i = new self(
                Id::fromString($data['articleId']), 
                new Slug($data['newSlug']));
        $i->updateFromArray($data);
        return $i;
    }

}