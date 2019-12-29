<?php

namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\ValueObject\Summary;
use Pmc\ObjectLib\Id;

class ChangeSummary extends ArticleCommand
{

    private $summary;

    public function __construct(Id $articleId, Summary $summary)
    {
        parent::__construct($articleId);
        $this->summary = $summary;
    }

    public function getSummary()
    {
        return $this->summary;
    }

}
