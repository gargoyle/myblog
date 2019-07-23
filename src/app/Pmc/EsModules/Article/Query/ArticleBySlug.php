<?php

namespace Pmc\EsModules\Article\Query;

use Pmc\Database\DatabaseCommandFailure;
use Pmc\Database\MysqlDb;
use Pmc\EsModules\Article\ValueObject\Slug;

class ArticleBySlug
{

    /**
     * @var Slug
     */
    private $slug;

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db, Slug $slug)
    {
        $this->db = $db;
        $this->slug = $slug;
    }

    public function result(): array
    {
        try {
            $strSlugIn = (string)$this->slug;
            $stmt = $this->db->prepareStatement("SELECT * FROM articles WHERE slug = ?");
            $stmt->bind_param("s", $strSlugIn);
            $row = $this->db->fetchSingleRow($stmt);
            
            $stmt = $this->db->prepareStatement("SELECT tag FROM articleTags WHERE articleId = ?");
            $stmt->bind_param("s", $row['articleId']);
            $rows = $this->db->fetchAllRows($stmt);
            
            $row['tags'] = implode(', ', array_map(function ($val){ return $val['tag']; }, $rows));
            return $row;
        } catch (DatabaseCommandFailure $e) {
            return [];
        }
    }
}
