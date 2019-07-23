<?php
namespace Pmc\EsModules\User\Query;

use Pmc\Database\MysqlDb;
use Pmc\ObjectLib\Id;


/**
 * @author Gargoyle <g@rgoyle.com>
 */
class UserDetailsById
{

    /**
     * @var Id
     */
    private $userId;

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db, Id $userId)
    {
        
        $this->db = $db;
        $this->userId = $userId;
    }

    public function result()
    {
        $inUserId = (string)$this->userId;
        $stmt = $this->db->prepareStatement("SELECT "
                . "userId, fullName, emailAddress, roles, username, lastUpdated, created "
                . "FROM userDetails WHERE userId = ?");
        $stmt->bind_param("s", $inUserId);
        $row = $this->db->fetchSingleRow($stmt);
        return $row;
    }
}
