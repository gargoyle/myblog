<?php
namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\ValueObject\Title;

class PublishArticle extends ArticleCommand
{

    public function __construct(\Pmc\ObjectLib\Id $articleId)
    {
        parent::__construct($articleId);
    }
}