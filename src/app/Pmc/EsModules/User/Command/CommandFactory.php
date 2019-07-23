<?php

namespace Pmc\EsModules\User\Command;

use InvalidArgumentException;
use Pmc\Database\RecordNotFoundException;
use Pmc\EsModules\User\Exception\AuthenticationFailed;
use Pmc\EsModules\User\Exception\DuplicateEmailAddressException;
use Pmc\EsModules\User\Exception\DuplicateUsernameException;
use Pmc\EsModules\User\Query\QueryFactory;
use Pmc\EsModules\User\ValueObject\EmailAddress;
use Pmc\EsModules\User\ValueObject\FullName;
use Pmc\EsModules\User\ValueObject\Passphrase;
use Pmc\EsModules\User\ValueObject\Username;
use Pmc\GigaFactory\Creator;
use Pmc\ObjectLib\Id;
use Pmc\ObjectLib\ParameterBag;

/**
 * @author Gargoyle <g@rgoyle.com>
 */
class CommandFactory implements Creator
{

    /**
     *
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function getCreatableList(): array
    {
        return [
            'User.CreateLoginToken',
            'User.Register',
            'User.AuthenticateWithPassphrase',
            'User.AuthenticateWithToken',
//            DestroySession::class
        ];
    }

    public function create(ParameterBag $params)
    {
        $params->require(['className']);
        switch ($params->get('className')) {
            case 'User.Register':
                return $this->createRegisterCommand($params);
                break;
            case 'User.AuthenticateWithPassphrase':
                return $this->createAuthenticateWithPassphraseCommand($params);
                break;
            case 'User.CreateLoginToken':
                return $this->createCreateLoginTokenCommand($params);
                break;
            case 'User.AuthenticateWithToken':
                return $this->createAuthenticateWithTokenCommand($params);
                break;
            default:
                throw new InvalidArgumentException(sprintf("Unable to create %s!", $params->get('className')));
                break;
        }
    }

    public function createCreateLoginTokenCommand(ParameterBag $params): CreateLoginToken
    {
        $params->require(['identification']);
        try {
            $params->add(['emailAddress' => $params->get('identification')]);
            $user = $this->queryFactory->createUserDetailsByEmailQuery($params)->result();
        } catch (InvalidArgumentException $e) {
        }
        
        try {
            $params->add(['username' => $params->get('identification')]);
            $user = $this->queryFactory->createUserDetailsByUsernameQuery($params)->result();
        } catch (InvalidArgumentException $e) {
        }
        
        
        if (empty($user)) {
            throw new AuthenticationFailed(sprintf(
                    "Unable to find user with email address or username: %s",
                    $params->get('identification')));
        }
        
        return new CreateLoginToken(Id::fromString($user['userId']));
    }

    private function createRegisterCommand(ParameterBag $params): Register
    {
        $params->require(['username', 'emailAddress', 'passphrase', 'fullName']);

        try {
            $userByEmail = $this->queryFactory->createUserDetailsByEmailQuery($params)->result();
            if (!empty($userByEmail)) {
                throw new DuplicateEmailAddressException(sprintf(
                                "Email address %s already exists, please use a different one.",
                                $params->get('emailAddress')));
            }
        } catch (RecordNotFoundException $e) {
            // OK to ignore as we are checking for duplicates.
        }

        try {
            $userByUsername = $this->queryFactory->createUserDetailsByUsernameQuery($params)->result();
            if (!empty($userByUsername)) {
                throw new DuplicateUsernameException(sprintf(
                                "Username %s already exists, please choose a different one.",
                                $params->get('username')));
            }
        } catch (RecordNotFoundException $e) {
            // OK to ignore as we are checking for duplicates.
        }
        
        $roles = ['Guest', 'Member'];
        $numUsers = $this->queryFactory->createUserCountQuery()->result();
        if ($numUsers == 0) {
            $roles[] = 'Admin';
        }

        return new Register(
                new Passphrase($params->get('passphrase')),
                new EmailAddress($params->get('emailAddress')),
                new FullName($params->get('fullName')),
                new Username($params->get('username')),
                $roles
        );
    }

    private function createAuthenticateWithPassphraseCommand(ParameterBag $params): AuthenticateWithPassphrase
    {

        $params->require(['identification', 'passphrase']);
        try {
            $params->add(['emailAddress' => $params->get('identification')]);
            $user = $this->queryFactory->createUserDetailsByEmailQuery($params)->result();
        } catch (InvalidArgumentException $e) {
        }
        
        try {
            $params->add(['username' => $params->get('identification')]);
            $user = $this->queryFactory->createUserDetailsByUsernameQuery($params)->result();
        } catch (InvalidArgumentException $e) {
        }
        
        
        if (empty($user)) {
            throw new AuthenticationFailed(sprintf(
                    "Unable to find user with email address or username: %s",
                    $params->get('identification')));
        }
        
        return new AuthenticateWithPassphrase(
                Id::fromString($user['userId']),
                new Passphrase($params->get('passphrase')));
    }
    
    public function createAuthenticateWithTokenCommand(ParameterBag $params): AuthenticateWithToken
    {
        $params->require(['token']);
        $token = Id::fromString($params->get('token'));
        try {
            $user = $this->queryFactory->createUserDetailsByLoginTokenQuery($token)->result();
        } catch (InvalidArgumentException $ex) {
        }
        
        
        if (empty($user)) {
            throw new AuthenticationFailed(sprintf("Supplied token is invalid!"));
        }
        
        return new AuthenticateWithToken(Id::fromString($user['userId']), $token);
    }
    
}
