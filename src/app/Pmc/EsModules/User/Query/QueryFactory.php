<?php

namespace Pmc\EsModules\User\Query;

use InvalidArgumentException;
use Pmc\Database\MysqlDb;
use Pmc\EsModules\User\ValueObject\EmailAddress;
use Pmc\EsModules\User\ValueObject\Username;
use Pmc\GigaFactory\Creator;
use Pmc\ObjectLib\Id;
use Pmc\ObjectLib\ParameterBag;

/**
 * @author gargoyle <g@rgoyle.com>
 */
class QueryFactory implements Creator
{

    /**
     * @var MysqlDb
     */
    private $db;

    public function __construct(MysqlDb $db)
    {
        $this->db = $db;
    }

    public function getCreatableList(): array
    {
        return [
            UserDetailsByEmail::class
        ];
    }
    
    public function create(ParameterBag $params)
    {
        $params->require(['className']);
        switch ($params->get('className')) {
            case UserDetailsByEmail::class:
                return $this->createUserDetailsByEmailQuery($params);
                break;
            case UserDetailsByUsername::class:
                return $this->createUserDetailsByUsernameQuery($params);
                break;
            case UserCount::class:
                return $this->createUserCountQuery($params);
                break;
            default:
                throw new InvalidArgumentException(sprintf("Unable to create %s!", $params->get('className')));
                break;
        }
    }
    
    public function createUserDetailsByEmailQuery(ParameterBag $params): UserDetailsByEmail
    {
        $params->require(['emailAddress']);
        return new UserDetailsByEmail(
                $this->db, 
                new EmailAddress($params->get('emailAddress')));
    }
    
    public function createUserDetailsByUsernameQuery(ParameterBag $params): UserDetailsByUsername
    {
        $params->require(['username']);
        return new UserDetailsByUsername(
                $this->db,
                new Username($params->get('username')));
    }
    
    public function createUserDetailsByIdQuery(Id $userId): UserDetailsById
    {
        return new UserDetailsById($this->db, $userId);
    }
    
    public function createUserDetailsByLoginTokenQuery(Id $token): UserDetailsByToken
    {
        return new UserDetailsByToken($this->db, $token);
    }

    public function createUserCountQuery(): UserCount
    {
        return new UserCount($this->db);
    }

}
