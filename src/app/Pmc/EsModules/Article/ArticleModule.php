<?php

namespace Pmc\EsModules\Article;

use Pmc\CommandBus\CommandBus;
use Pmc\Database\MysqlDb;
use Pmc\EsModules\Article\Handler\ArticleHandler;
use Pmc\EsModules\Article\Command\CommandFactory;
use Pmc\EventSourceLib\Storage\EventStore;
use Pmc\GigaFactory\GigaFactory;
use Pmc\MessageBus\MessageBus;

/**
 * @author Paul Court <emails@paulcourt.co.uk>
 */
class ArticleModule
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
     *
     * @var CommandFactory
     */
    private $commandFactory;

    /**
     *
     * @var Query\QueryFactory
     */
    private $queryFactory;

    
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
        $this->factory = $factory;
        
        $this->registerFactories();
        $this->registerListeners();
        $this->registerHandlers();
    }

    protected function registerFactories(): void
    {
        $this->queryFactory = new Query\QueryFactory($this->db);
        $this->commandFactory = new CommandFactory($this->queryFactory);
        
        $this->factory->register($this->commandFactory);
        $this->factory->register($this->queryFactory);        
    }
    
    protected function registerListeners(): void
    {
        $this->messageBus->addListener(new Projection\Articles($this->db));
    }

    private function registerHandlers(): void
    {
        $this->commandBus->addHandler(new ArticleHandler($this->eventStore));
    }

    
    public function rebuildArticlesProjections(): void
    {
        $projection = new Projection\Articles($this->db);
        $projection->reset();
        foreach ($this->eventStore->getAllEvents() as $eventStream) {
            $projection->replay($eventStream);
        }
    }
}
