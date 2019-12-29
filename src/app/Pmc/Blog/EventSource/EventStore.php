<?php

namespace Pmc\Blog\EventSource;

use Pmc\EventSourceLib\Event\EventStream;
use Pmc\EventSourceLib\Event\EventStreamException;
use Pmc\EventSourceLib\Event\StreamEvent;
use Pmc\EventSourceLib\Storage\EventStore as AbstractEventStore;
use Pmc\EventSourceLib\Storage\StorageEngine;
use Pmc\MessageBus\MessageBus;
use Pmc\ObjectLib\ClassNameMap;
use Pmc\ObjectLib\Id;

/**
 * @author Paul Court <emails@paulcourt.co.uk>
 */
class EventStore extends AbstractEventStore
{

    /**
     * @var ClassNameMap
     */
    private $eventNameMapper;

    /**
     * @var StorageEngine
     */
    private $storageEngine;

    public function __construct(StorageEngine $storageEngine, MessageBus $messageBus, ClassNameMap $eventNameMapper)
    {
        parent::__construct($storageEngine,
                $messageBus,
                $eventNameMapper);
        
        $this->storageEngine = $storageEngine;
        $this->eventNameMapper = $eventNameMapper;
    }

    public function store(EventStream $events): void
    {
        parent::store($events);
    }

    protected function getMetaData(): array
    {
        return [];
    }

    public function getAllEvents()
    {
        $rawStream = $this->storageEngine->getAllStreams();
        $eventStream = null;
        
        foreach ($rawStream as $serialisedEventRecord) {
            $streamId = Id::fromString($serialisedEventRecord['streamId']);
            $domainEventClass = $this->eventNameMapper->getClassForName($serialisedEventRecord['eventName']);
            $domainEvent = $domainEventClass::fromArray(json_decode($serialisedEventRecord['eventData'], true));
            $streamEvent = new StreamEvent($streamId, $serialisedEventRecord['streamSeq'], $domainEvent);
            
            if ($eventStream == null) {
                $eventStream = new EventStream($streamId);
            }
            
            try {
                $eventStream->addEvent($streamEvent);
            } catch (EventStreamException $e) {
                yield $eventStream;
                $eventStream = new EventStream($streamId);
                $eventStream->addEvent($streamEvent);
            }
        }
        
        yield $eventStream;
    }

}
