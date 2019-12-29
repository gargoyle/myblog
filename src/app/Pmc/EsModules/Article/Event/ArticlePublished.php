<?php

namespace Pmc\EsModules\Article\Event;

use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

class ArticlePublished extends AbstractDomainEvent
{

    /**
     * @var Id
     */
    private $articleId;

    public function __construct(Id $articleId)
    {
        parent::__construct();

        $this->articleId = $articleId;
    }

    public function getArticleId(): Id
    {
        return $this->articleId;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['articleId'] = (string) $this->articleId;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $i = new self(Id::fromString($data['articleId']));
        $i->updateFromArray($data);
        return $i;
    }

}
