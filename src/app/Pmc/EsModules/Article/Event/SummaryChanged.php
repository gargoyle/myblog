<?php

namespace Pmc\EsModules\Article\Event;

use Pmc\EsModules\Article\ValueObject\Summary;
use Pmc\EventSourceLib\Event\AbstractDomainEvent;
use Pmc\ObjectLib\Id;

class SummaryChanged extends AbstractDomainEvent
{

    /**
     *
     * @var Summary
     */
    private $summary;

    /**
     * @var Id
     */
    private $articleId;

    public function __construct(Id $articleId, Summary $summary)
    {
        parent::__construct();

        $this->articleId = $articleId;
        $this->summary = $summary;
    }

    public function getSummary(): Summary
    {
        return $this->summary;
    }

    public function getArticleId(): Id
    {
        return $this->articleId;
    }

    public function toArray(): array
    {
        $data = parent::toArray();
        $data['articleId'] = (string) $this->articleId;
        $data['summary'] = (string) $this->summary;
        return $data;
    }

    public static function fromArray(array $data)
    {
        $i = new self(
                Id::fromString($data['articleId']),
                new Summary($data['summary']));
        $i->updateFromArray($data);
        return $i;
    }

}
