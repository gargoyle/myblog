<?php

namespace Pmc\EsModules\Article\Query;

use InvalidArgumentException;
use Pmc\Database\MysqlDb;
use Pmc\EsModules\Article\ValueObject\Slug;
use Pmc\GigaFactory\Creator;
use Pmc\ObjectLib\Exception\MissingBagItemException;
use Pmc\ObjectLib\Id;
use Pmc\ObjectLib\ParameterBag;

class QueryFactory implements Creator
{

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db)
    {
        $this->db = $db;
    }

    public function getCreatableList(): array
    {
        return [
            ArticleList::class,
            ArticleBySlug::class,
            ArticleById::class,
        ];
    }

    public function create(ParameterBag $params)
    {
        $params->require(['className']);
        switch ($params->get('className')) {
            case ArticleList::class:
                return $this->createArticleList($params);
                break;
            case ArticleBySlug::class:
                return $this->createArticleBySlug($params);
                break;
            case ArticleById::class:
                return $this->createArticleById($params);
                break;
            default:
                throw new InvalidArgumentException(sprintf("Unable to create %s!", $params->get('className')));
        }
    }

    public function createArticleList(ParameterBag $params): ArticleList
    {
        try {
            $includeUnpublished = (bool)$params->get('includeUnpublished');
        } catch (MissingBagItemException $e) {
            $includeUnpublished = false;
        }
        
        return new ArticleList($this->db, $includeUnpublished);
    }
    
    public function createArticleBySlug(ParameterBag $params)
    {
        $params->require(['slug']);
        return new ArticleBySlug($this->db, new Slug($params->get('slug')));
    }

    public function createArticleById(ParameterBag $params)
    {
        $params->require(['articleId']);
        return new ArticleById($this->db, Id::fromString($params->get('articleId')));
    }
}
