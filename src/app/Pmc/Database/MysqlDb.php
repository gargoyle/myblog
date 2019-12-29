<?php

namespace Pmc\Database;

use mysqli;
use mysqli_stmt;


class MysqlDb
{
    private $password;
    private $username;
    private $dbname;
    private $host;
    
    /**
     *
     * @var mysqli
     */
    private $connection;

    public function __construct(string $host, string $dbname, string $username, string $password)
    {
        $this->host = $host;
        $this->dbname = $dbname;
        $this->username = $username;
        $this->password = $password;
    }

    public function getConnection(): mysqli
    {
        
        if ($this->connection instanceof mysqli) {
            $this->connection->ping();
            $connStats = $this->connection->get_connection_stats();
        }
        
        if (empty($connStats)) {
            $mysqli = mysqli_init();
            $mysqli->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, 1);
            $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 2);
            $mysqli->options(MYSQLI_CLIENT_COMPRESS, 1);

            $mysqli->real_connect(
                    $this->host,
                    $this->username,
                    $this->password,
                    $this->dbname);
            if ($mysqli->connect_errno) {
                throw new DatabaseConnectionFailure(sprintf(
                        "Failed to connect to MySQL Server: (%s) %s",
                        $mysqli->connect_errno,
                        $mysqli->connect_error
                        ));
            }

            $mysqli->set_charset('utf8mb4');
            $this->connection = $mysqli;
        }
        
        return $this->connection;
    }
    
    public function prepareStatement(string $sql): mysqli_stmt
    {
        $mysqli = $this->getConnection();
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            throw new DatabaseCommandFailure(sprintf(
                    "Failed to prepare SQL statement! (%s: %s)",
                    $mysqli->connect_errno, $mysqli->connect_error));
        }
        return $stmt;
    }
    
    
    public function fetchAllRows(mysqli_stmt $stmt): array
    {
        if (!$stmt->execute()) {
            throw new DatabaseCommandFailure(sprintf(
                    "Failed to execute query! (%s: %s)",
                    $stmt->errno, $stmt->error));
        }
        $res = $stmt->get_result();
        $rows = [];
        while ($row = $res->fetch_assoc()) {
            $rows[] = $row;
        }
        $stmt->close();
        return $rows;
    }
    
    public function fetchSingleRow(mysqli_stmt $stmt): array
    {
        if (!$stmt->execute()) {
            throw new DatabaseCommandFailure(sprintf(
                    "Failed to execute query! (%s: %s)",
                    $stmt->errno, $stmt->error));
        }
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        
        if (empty($row)) {
            throw new RecordNotFoundException("record not found");
        }
        return $row;
    }
    
    public function executeNonReturn(mysqli_stmt $stmt, bool $closeStmt = true)
    {
        if (!$stmt->execute()) {
            if ($stmt->errno == 1062) {
                throw new PrimaryKeyConflict(sprintf("Unique key has already been used! (%s)", $stmt->error));
            }
            
            throw new DatabaseCommandFailure(sprintf(
                    "Failed to execute query! (%s: %s)",
                    $stmt->errno, $stmt->error));
        }
        
        if ($closeStmt) {
            $stmt->close();
        }
    }
}
