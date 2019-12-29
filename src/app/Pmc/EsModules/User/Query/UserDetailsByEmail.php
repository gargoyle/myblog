<?php

namespace Pmc\EsModules\User\Query;

use Pmc\Database\MysqlDb;
use Pmc\EsModules\User\ValueObject\EmailAddress;

/**
 * @author Gargoyle <g@rgoyle.com>
 */
class UserDetailsByEmail
{

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db, EmailAddress $emailAddress)
    {
        $this->db = $db;
        $this->emailAddress = $emailAddress;
    }

    public function result()
    {
        $inEmailAddress = (string)$this->emailAddress;
        
        $stmt = $this->db->prepareStatement("SELECT "
                . "userId, fullName, emailAddress, roles, username, lastUpdated, created "
                . "FROM userDetails WHERE emailAddress = ?");
        $stmt->bind_param("s", $inEmailAddress);
        $row = $this->db->fetchSingleRow($stmt);
        return $row;
    }
}
