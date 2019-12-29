<?php
namespace Pmc\EsModules\User\Query;

use Pmc\Database\MysqlDb;
use Pmc\ObjectLib\Id;


/**
 * @author Gargoyle <g@rgoyle.com>
 */
class UserDetailsByToken
{

    /**
     * @var Id
     */
    private $token;

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db, Id $token)
    {
        
        $this->db = $db;
        $this->token = $token;
    }

    public function result()
    {
        $inToken = (string)$this->token;
        $stmt = $this->db->prepareStatement("SELECT "
                . "userId, fullName, emailAddress, roles, username, lastUpdated, created "
                . "FROM userDetails WHERE loginToken = ?");
        $stmt->bind_param("s", $inToken);
        $row = $this->db->fetchSingleRow($stmt);
        return $row;
    }
}
