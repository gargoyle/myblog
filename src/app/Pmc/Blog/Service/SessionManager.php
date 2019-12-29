<?php

namespace Pmc\Blog\Service;

use Pmc\Database\RecordNotFoundException;
use Pmc\EsModules\User\Event\AuthenticationSucceeded;
use Pmc\EsModules\User\Event\UserRegistered;
use Pmc\EsModules\User\Query\QueryFactory;
use Pmc\MessageBus\Listener;
use Pmc\ObjectLib\Id;
use Pmc\Session\Session;
use Pmc\Session\SessionProfile;
use Psr\Log\LoggerInterface;


/**
 * @author Gargoyle <g@rgoyle.com>
 */
class SessionManager implements Listener
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var QueryFactory
     */
    private $userQueryFactory;

    /**
     * @var Session
     */
    private $session;
    
    

    public function __construct(Session $session, QueryFactory $factory, LoggerInterface $logger)
    {
        $this->session = $session;
        $this->userQueryFactory = $factory;
        $this->logger = $logger;
        
        if (!empty($_SESSION['Pmc.Blog.CurrentLoginId'])) {
            $this->logger->debug("Found user id in session storage, attempting to load");
            $this->findUserAndSetSessionProfile(Id::fromString($_SESSION['Pmc.Blog.CurrentLoginId']));
        } 
    }

    public function getObservables(): array
    {
        return [
            AuthenticationSucceeded::class,
            UserRegistered::class
        ];
    }

    public function notify($message): void
    {
        switch (get_class($message)) {
            case AuthenticationSucceeded::class:
                $this->notifyAuthenticationSucceededEvent($message);
                break;
            case UserRegistered::class:
                $this->notifyUserRegisteredEvent($message);
                break;
            default:
                break;
        }
    }
    
    private function findUserAndSetSessionProfile(Id $userId)
    {
        try {
            $user = $this->userQueryFactory->createUserDetailsByIdQuery($userId)->result();
            $this->session->setProfile(new SessionProfile(
                    (string) $user['userId'],
                    (string) $user['username'],
                    explode(',', $user['roles'])));

            $_SESSION['Pmc.Blog.CurrentLoginId'] = (string) $user['userId'];
            $this->logger->debug(sprintf("Successfully set session user to: %s", (string) $user['userId']));
        } catch (RecordNotFoundException $ex) {
            $this->logger->warning("User not found, setting guest profile.");
            $this->session->setProfile(SessionProfile::guestProfile());
        }  
    }
    
    protected function notifyAuthenticationSucceededEvent(AuthenticationSucceeded $event): void
    {
        $this->findUserAndSetSessionProfile($event->getUserId());
    }

    protected function notifyUserRegisteredEvent(UserRegistered $event)
    {
        $this->findUserAndSetSessionProfile($event->getUserId());
    }
}
