<?php
namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\ValueObject\Title;

class ChangeTags extends ArticleCommand
{

    /**
     * @var string
     */
    private $tags;

    public function __construct(\Pmc\ObjectLib\Id $articleId, string $tags)
    {
        parent::__construct($articleId);
        $this->tags = $tags;
    }


    public function getTags()
    {
        return $this->tags;
    }




}