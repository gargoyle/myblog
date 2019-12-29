<?php

namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\ValueObject\Url;
use Pmc\ObjectLib\Id;

class ChangeOpenGraphImage extends ArticleCommand
{
    private $url;
    
    public function __construct(Id $articleId, Url $url)
    {
        parent::__construct($articleId);
        $this->url = $url;
    }
    
    public function getUrl(): Url
    {
        return $this->url;
    }
}
