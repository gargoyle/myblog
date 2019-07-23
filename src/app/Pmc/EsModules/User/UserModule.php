<?php

namespace Pmc\EsModules\User;

use Pmc\CommandBus\CommandBus;
use Pmc\CommandBus\Handler;
use Pmc\Database\MysqlDb;
use Pmc\EsModules\User\Aggregate\User;
use Pmc\EsModules\User\Command\AuthenticateWithPassphrase;
use Pmc\EsModules\User\Command\AuthenticateWithToken;
use Pmc\EsModules\User\Command\CommandFactory;
use Pmc\EsModules\User\Command\CreateLoginToken;
use Pmc\EsModules\User\Command\Register;
use Pmc\EsModules\User\Projection\UserDetails;
use Pmc\EsModules\User\Query\QueryFactory;
use Pmc\EventSourceLib\Storage\EventStore;
use Pmc\GigaFactory\GigaFactory;
use Pmc\MessageBus\MessageBus;
use RuntimeException;

/**
 * @author Paul Court <emails@paulcourt.co.uk>
 */
class UserModule implements Handler
{

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var GigaFactory
     */
    private $factory;

    /**
     * @var MysqlDb
     */
    private $db;

    /**
     * @var MessageBus
     */
    private $messageBus;

    /**
     * @var EventStore
     */
    private $eventStore;
    
    /**
     * @var QueryFactory
     */
    private $queryFactory;
    
    /**
     * @var CommandFactory
     */
    private $commandFactory;
    
    public function __construct(
            MysqlDb $db,
            EventStore $eventStore, 
            MessageBus $messageBus,
            CommandBus $commandBus,
            GigaFactory $factory)
    {
        $this->eventStore = $eventStore;
        $this->messageBus = $messageBus;
        $this->commandBus = $commandBus;
        $this->db = $db;
        $this->queryFactory = new QueryFactory($db);
        $this->commandFactory = new CommandFactory(new QueryFactory($db));
        
        $this->factory = $factory;
        $this->factory->register($this->queryFactory);
        $this->factory->register($this->commandFactory);
        
        $this->registerListeners();
        $this->registerHandlers();
    }

    public function rebuildUserProjections(): void
    {
        $projection = new UserDetails($this->db);
        $projection->reset();
        foreach ($this->eventStore->getAllEvents() as $eventStream) {
            $projection->replay($eventStream);
        }
    }
    
    private function registerListeners(): void
    {
        $this->messageBus->addListener(new UserDetails($this->db));
    }

    private function registerHandlers(): void
    {
        $this->commandBus->addHandler($this);
    }
    
    public function getQueryFactory(): QueryFactory
    {
        return $this->queryFactory;
    }
    
    private function registerUser(Register $command): void
    {
        $events = User::register(
                $command->getId(),
                $command->getPassphrase(),
                $command->getEmailAddress(),
                $command->getFullName(),
                $command->getUsername(),
                $command->getRoles());
        $this->eventStore->store($events);
    }
    

    public function getSupportedCommands(): array
    {
        return [
            Register::class,
            AuthenticateWithPassphrase::class,
            AuthenticateWithToken::class,
            CreateLoginToken::class
        ];
    }

    public function handleCommand($command): void
    {
        if ($command instanceof Register) {
            $this->registerUser($command);
        } else {
            $eventStream = $this->eventStore->getStream($command->getUserId());
            $user = new User($eventStream);
            
            switch (true) {
                case $command instanceof AuthenticateWithPassphrase:
                    $events = $user->authenticate($command->getPassphrase());
                    $this->eventStore->store($events);
                    break;
                case $command instanceof AuthenticateWithToken:
                    $events = $user->authenticateWithToken($command->getToken());
                    $this->eventStore->store($events);
                    break;
                case $command instanceof CreateLoginToken:
                    $events = $user->createLoginToken($command);
                    $this->eventStore->store($events);
                    break;
                default:
                    throw new RuntimeException(printf(
                            "Command handler is unable to process commands of type %s",
                            get_class($command)));
                    break;
            }
        }
    }

}
