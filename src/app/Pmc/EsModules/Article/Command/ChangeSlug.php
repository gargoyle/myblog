<?php
namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\ValueObject\Slug;
use Pmc\ObjectLib\Id;

class ChangeSlug extends ArticleCommand
{

    /**
     * @var Slug
     */
    private $slug;

    public function __construct(Id $articleId, Slug $slug)
    {
        parent::__construct($articleId);
        $this->slug = $slug;
    }
    
    public function getSlug(): Slug
    {
        return $this->slug;
    }
}