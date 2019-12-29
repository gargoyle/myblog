<?php

namespace Pmc\EsModules\Article\Query;

use Pmc\Database\DatabaseCommandFailure;
use Pmc\Database\MysqlDb;
use Pmc\ObjectLib\Id;

class ArticleById
{

    /**
     * @var Id
     */
    private $id;

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db, Id $id)
    {
        $this->db = $db;
        $this->id = $id;
    }

    public function result(): array
    {
        try {
            $strIdIn = (string)$this->id;
            $stmt = $this->db->prepareStatement("SELECT * FROM articles WHERE articleId = ?");
            $stmt->bind_param("s", $strIdIn);
            $row = $this->db->fetchSingleRow($stmt);
            
            $stmt = $this->db->prepareStatement("SELECT tag FROM articleTags WHERE articleId = ?");
            $stmt->bind_param("s", $strIdIn);
            $rows = $this->db->fetchAllRows($stmt);
            
            $row['tags'] = implode(', ', array_map(function ($val){ return $val['tag']; }, $rows));
            return $row;
        } catch (DatabaseCommandFailure $e) {
            return [];
        }
    }
}
