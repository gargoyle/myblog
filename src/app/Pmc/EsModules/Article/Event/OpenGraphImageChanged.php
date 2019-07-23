<?php

namespace Pmc\EsModules\Article\Event;

use Pmc\EsModules\Article\ValueObject\Url;
use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

class OpenGraphImageChanged extends AbstractDomainEvent
{

    /**
     * @var Url
     */
    private $newUrl;

    /**
     * @var Id
     */
    private $articleId;

    public function __construct(Id $articleId, Url $newUrl)
    {
        parent::__construct();
        $this->articleId = $articleId;
        $this->newUrl = $newUrl;
    }
    
    public function getNewUrl(): Url
    {
        return $this->newUrl;
    }

    public function getArticleId(): Id
    {
        return $this->articleId;
    }

        
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['articleId'] = (string) $this->articleId;
        $data['newUrl'] = (string) $this->newUrl;
        return $data;
    }
    
    public static function fromArray(array $data)
    {
        $i = new self(
                Id::fromString($data['articleId']), 
                new Url($data['newUrl']));
        $i->updateFromArray($data);
        return $i;
    }

}
