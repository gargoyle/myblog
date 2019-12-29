<?php
namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\ValueObject\Title;

class ChangeTitle extends ArticleCommand
{

    /**
     * @var Title
     */
    private $title;

    public function __construct(\Pmc\ObjectLib\Id $articleId, Title $title)
    {
        parent::__construct($articleId);
        $this->title = $title;
    }
    
    public function getTitle(): Title
    {
        return $this->title;
    }


}