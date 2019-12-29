<?php

namespace Pmc\EsModules\User\Projection;

use Pmc\Database\MysqlDb;
use Pmc\EsModules\User\Event\LoginTokenCreated;
use Pmc\EsModules\User\Event\UserRegistered;
use Pmc\EventSourceLib\Event\EventStream;
use Pmc\MessageBus\Listener;



/**
 * Creates and/or updates full user details table from user events
 *
 * @author Gargoyle <g@rgoyle.com>
 */
class UserDetails implements Listener
{

    /**
     * @var MysqlDb
     */
    private $db;
    private $tableCreated = false;

    public function __construct(MysqlDb $db)
    {
        $this->db = $db;
    }

    public function getObservables(): array
    {
        return [
            UserRegistered::class,
            LoginTokenCreated::class
        ];
    }

    public function notify($message): void
    {
        switch (get_class($message)) {
            case UserRegistered::class:
                $this->notifyUserRegisteredEvent($message);
                break;
            case LoginTokenCreated::class:
                $this->notifyLoginTokenCreatedEvent($message);
                break;
            default:
                return;
        }
    }
    
   
    public function reset(): void
    {
        $this->dropTable();
        $this->createTableIfNeeded();
    }

    public function replay(EventStream $stream): void
    {
        foreach ($stream as $event) {
            $this->notify($event->getEvent());
        }
    }

    private function dropTable(): void
    {
        $sql = 'DROP TABLE IF EXISTS userDetails';
        $stmt = $this->db->prepareStatement($sql);
        $this->db->executeNonReturn($stmt);
        $this->tableCreated = false;
    }
    
    private function createTableIfNeeded(): void
    {
        if ($this->tableCreated) {
            return;
        }

        $sql = '
CREATE TABLE `userDetails` (
    `userId` char(64) COLLATE utf8mb4_bin NOT NULL,
    `created` DECIMAL(18,4) NOT NULL,
    `lastUpdated` DECIMAL(18,4) NOT NULL,
    `username` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `password` char(255) COLLATE utf8mb4_bin NOT NULL,
    `roles` char(255) COLLATE utf8mb4_bin,
    `emailAddress` char(150) COLLATE utf8mb4_unicode_ci NOT NULL,
    `fullName` char(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `loginToken` char(64) COLLATE utf8mb4_bin NULL,
    PRIMARY KEY (`userId`),
    UNIQUE KEY `EMAIL` (`emailAddress`),
    UNIQUE KEY `UNAME` (`username`),
    UNIQUE KEY `LTOKEN` (`loginToken`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin';
        $stmt = $this->db->prepareStatement($sql);
        $this->db->executeNonReturn($stmt);

        $this->tableCreated = true;
    }
    
    /*



     */
    
    protected function notifyUserRegisteredEvent(UserRegistered $event)
    {
        $inUserId = (string)$event->getUserId();
        $inLastUpdated = $event->getTimestamp();
        $inCreated = $event->getTimestamp();
        $inUsername = (string)$event->getUsername();
        $inPassword = (string)$event->getPasswordHash();
        $inRoles = (string)implode(',', $event->getRoles());
        $inEmailAddress = (string)$event->getEmailAddress();
        $inFullname = (string)$event->getFullName();
        
        $stmt = $this->db->prepareStatement('INSERT INTO userDetails ('
                . 'userId,'
                . 'created,'
                . 'lastUpdated,'
                . 'username,'
                . 'password,'
                . 'roles,'
                . 'emailAddress,'
                . 'fullName'
                . ') VALUES (?,?,?,?,?,?,?,?)');
        $stmt->bind_param('ssdsssss', 
                $inUserId, 
                $inCreated,
                $inLastUpdated, 
                $inUsername, 
                $inPassword, 
                $inRoles, 
                $inEmailAddress, 
                $inFullname);
        $this->db->executeNonReturn($stmt);
    }

    protected function notifyLoginTokenCreatedEvent(LoginTokenCreated $event)
    {
        $inLoginToken = (string)$event->getToken();
        $inUserId = (string)$event->getUserId();
        $inLastUpdated = $event->getTimestamp();
        $stmt = $this->db->prepareStatement(
                'UPDATE userDetails SET loginToken = ?, lastUpdated = ? WHERE userId = ?'
                );
        $stmt->bind_param('sss', $inLoginToken, $inLastUpdated, $inUserId);
        $this->db->executeNonReturn($stmt);
    }

    
}
