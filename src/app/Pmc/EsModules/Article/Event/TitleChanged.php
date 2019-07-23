<?php

namespace Pmc\EsModules\Article\Event;

use Pmc\EsModules\Article\ValueObject\Title;
use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

class TitleChanged extends AbstractDomainEvent
{

    /**
     * @var Title
     */
    private $newTitle;

    /**
     * @var Id
     */
    private $articleId;

    public function __construct(Id $articleId, Title $newTitle)
    {
        parent::__construct();
        
        $this->articleId = $articleId;
        $this->newTitle = $newTitle;
    }
    
    public function getNewTitle(): Title
    {
        return $this->newTitle;
    }

    public function getArticleId(): Id
    {
        return $this->articleId;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['articleId'] = (string) $this->articleId;
        $data['newTitle'] = (string) $this->newTitle;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $i = new self(
                Id::fromString($data['articleId']), 
                new Title($data['newTitle']));
        $i->updateFromArray($data);
        return $i;
    }

}