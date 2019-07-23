<?php

namespace Pmc\EsModules\Article\Query;

use Pmc\Database\DatabaseCommandFailure;
use Pmc\Database\MysqlDb;

class ArticleList
{

    /**
     * @var MysqlDb
     */
    private $db;
    private $includeUnpublished;

    public function __construct(MysqlDb $db, bool $includeUnpublished = false)
    {
        $this->db = $db;
        $this->includeUnpublished = $includeUnpublished;
    }

    public function result(): array
    {
        try {
            $stmt = $this->db->prepareStatement(""
                    . "SELECT a.*, GROUP_CONCAT(at.tag SEPARATOR ', ') as tags "
                    . "FROM articles a "
                    . "LEFT JOIN articleTags at ON a.articleId = at.articleId "
                    . " " . $this->where()
                    . "GROUP BY a.articleId "
                    . "ORDER BY (a.published = 0) ASC, a.published DESC, a.lastUpdated DESC");
            return $this->db->fetchAllRows($stmt);
        } catch (DatabaseCommandFailure $e) {
            return [];
        }
    }

    private function where(): string
    {
        $where = "";
        if (!$this->includeUnpublished) {
            $where .= " a.published IS NOT NULL ";
        }
        
        if (!empty($where)) {
            return "WHERE {$where}";
        }
        
        return "";
    }
}
