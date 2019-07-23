<?php
namespace Pmc\EsModules\User\Query;

use Pmc\Database\MysqlDb;
use Pmc\ObjectLib\Id;


/**
 * @author Gargoyle <g@rgoyle.com>
 */
class UserCount
{

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db)
    {
        $this->db = $db;
    }

    public function result(): int
    {
        $stmt = $this->db->prepareStatement("SELECT count(*) AS numUsers FROM userDetails");
        $row = $this->db->fetchSingleRow($stmt);
        return (int)$row['numUsers'];
    }
}
