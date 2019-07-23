<?php
namespace Pmc\EsModules\User\Query;

use Pmc\Database\MysqlDb;
use Pmc\EsModules\User\ValueObject\Username;



/**
 * @author Gargoyle <g@rgoyle.com>
 */
class UserDetailsByUsername
{

    /**
     * @var Username
     */
    private $username;

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db, Username $username)
    {
        
        $this->db = $db;
        $this->username = $username;
    }

    public function result()
    {
        $inUsername = (string)$this->username;
        $stmt = $this->db->prepareStatement("SELECT "
                . "userId, fullName, emailAddress, roles, username, lastUpdated, created "
                . "FROM userDetails WHERE username = ?");
        $stmt->bind_param("s", $inUsername);
        $row = $this->db->fetchSingleRow($stmt);
        return $row;
    }
}
