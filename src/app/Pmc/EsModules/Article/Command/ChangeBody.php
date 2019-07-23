<?php

namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\ValueObject\Body;
use Pmc\ObjectLib\Id;

class ChangeBody extends ArticleCommand
{

    /**
     * @var Body
     */
    private $body;

    public function __construct(Id $articleId, Body $body)
    {
        parent::__construct($articleId);
        $this->body = $body;
    }

    public function getBody(): Body
    {
        return $this->body;
    }

}
