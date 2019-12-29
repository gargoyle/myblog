<?php

namespace Pmc\Blog;

use Aptoma\Twig\Extension\MarkdownEngine\MichelfMarkdownEngine;
use Aptoma\Twig\Extension\MarkdownExtension;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Pmc\Blog\EventSource\EventNameClassMap;
use Pmc\Blog\EventSource\EventStorageEngine;
use Pmc\Blog\EventSource\EventStore;
use Pmc\Blog\Service\SessionManager;
use Pmc\CommandBus\CommandBus;
use Pmc\Database\MysqlDb;
use Pmc\EsModules\Article\ArticleModule;
use Pmc\EsModules\User\UserModule;
use Pmc\GigaFactory\GigaFactory;
use Pmc\MessageBus\MessageBus;
use Pmc\ObjectLib\ClassNameMap;
use Pmc\Session\Session;
use Psr\Log\LoggerInterface;
use Twig\Extension\DebugExtension;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_SimpleFunction;
use const APP_ROOT;

class AppContainer
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var array
     */
    private $config;

    /**
     * @var MessageBus
     */
    private $messageBus;
    
    /**
     *
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EventNameClassMap
     */
    private $eventNameClassMap;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserModule
     */
    private $userModule;
    
    /**
     *
     * @var ArticleModule
     */
    private $articles;

    /**
     * @var SessionManager
     */
    private $sessionManager;
    
    /**
     * @var GigaFactory
     */
    private $factory;
    
    /**
     * @var Twig_Environment
     */
    private $twig;
    
    /**
     *
     * @var \Domnikl\Statsd\Client
     */
    private $statsdClient;
    
    public function __construct()
    {
        $configFilename = APP_ROOT . '/' . getenv('CONFIG_FILE');
        $fromIniFile = [];
        if (file_exists($configFilename)) {
            $fromIniFile = parse_ini_file($configFilename, true, INI_SCANNER_TYPED);
        }
        $this->config = $fromIniFile;

        $this->logger = new Logger('APP_LOG', [new StreamHandler('/tmp/blog.log')]);

        $this->messageBus = new MessageBus($this->logger);
        $this->commandBus = new CommandBus();
        $this->eventNameClassMap = new EventNameClassMap();
        $this->factory = new GigaFactory($this->logger);

        $this->userModule = new UserModule(
                $this->getDatabase(), 
                $this->getEventStore(), 
                $this->getMessageBus(), 
                $this->getCommandBus(),
                $this->getFactory());
        
        $this->articles = new ArticleModule(
                $this->getDatabase(), 
                $this->getEventStore(), 
                $this->getMessageBus(),
                $this->getCommandBus(),
                $this->getFactory());

        $this->sessionManager = new SessionManager(
                $this->getSession(), 
                $this->userModule->getQueryFactory(), 
                $this->logger);
        
        $this->messageBus->addListener($this->sessionManager);
    }

    public function rebuildProjections()
    {
        $this->articles->rebuildArticlesProjections();
        $this->userModule->rebuildUserProjections();
    }
    
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }
    
    public function getStatsdClient()
    {
        if ($this->statsdClient == null) {
            $connection = new \Domnikl\Statsd\Connection\UdpSocket();
            $statsd = new \Domnikl\Statsd\Client($connection, "my.blog");
            $this->statsdClient = $statsd;
        }
        return $this->statsdClient;
    }
    
    public function twig(): Twig_Environment
    {
        if ($this->twig == null) {
            $loader = new Twig_Loader_Filesystem(APP_ROOT . '/views');
            
            if ($this->config['httpHandler']['debug']) {
                $this->twig = new Twig_Environment($loader, [
                    'cache' => false,
                    'optimizations' => 0,
                    'debug' => true
                ]);
                $this->twig->addExtension(new DebugExtension());
            } else {
                $this->twig = new Twig_Environment($loader, [
                    'cache' => '/tmp/twigcache',
                    'optimizations' => 1
                ]);
            }
            
            $this->twig->addGlobal('global', [
                'appServedFrom' => gethostname(),
                'profile' => $this->getSession()->profile(),
                'twitterHandle' => "@paulcourt101"
            ]);

            $this->twig->addExtension(new MarkdownExtension(new MichelfMarkdownEngine()));
            $this->twig->addFunction(
                    new Twig_SimpleFunction('microtimeDate', function ($microtime) {
                        return date('d/m/Y H:i', intval($microtime));
                    })
            );
        }
        return $this->twig;
    }
    
    public function getSession(): Session
    {
        if ($this->session == null) {
            $this->session = new Session(session_id());
        }
        return $this->session;
    }

    public function getDatabase(): MysqlDb
    {
        return new MysqlDb(
                $this->config['database']['host'],
                $this->config['database']['dbname'],
                $this->config['database']['user'],
                $this->config['database']['password']);
    }

    public function getMessageBus(): MessageBus
    {
        return $this->messageBus;
    }
    
    public function getCommandBus(): CommandBus
    {
        return $this->commandBus;
    }
    
    public function getClassMap(): ClassNameMap
    {
        return $this->eventNameClassMap;
    }

    public function getEventStore(): EventStore
    {
        $eventStore = new EventStore(
                new EventStorageEngine($this->getDatabase()),
                $this->getMessageBus(),
                $this->getClassMap());
        return $eventStore;
    }

    public function getFactory(): GigaFactory
    {
        if ($this->factory == null) {
            $this->factory = new GigaFactory($this->logger);
        }
        return $this->factory;
    }

}
