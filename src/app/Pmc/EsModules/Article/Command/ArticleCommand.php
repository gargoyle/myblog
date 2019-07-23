<?php

namespace Pmc\EsModules\Article\Command;

/**
 * Base class for all article commands
 */
abstract class ArticleCommand
{

    /**
     * @var \Pmc\ObjectLib\Id
     */
    private $articleId;

    public function __construct(\Pmc\ObjectLib\Id $articleId)
    {
        $this->articleId = $articleId;
    }
    
    public function getArticleId(): \Pmc\ObjectLib\Id
    {
        return $this->articleId;
    }


}
