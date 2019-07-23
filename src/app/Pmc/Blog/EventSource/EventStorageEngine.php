<?php

namespace Pmc\Blog\EventSource;

/**
 * @author Paul Court <emails@paulcourt.co.uk>
 */
class EventStorageEngine implements \Pmc\EventSourceLib\Storage\StorageEngine
{

    /**
     * @var \Pmc\Database\MysqlDb
     */
    private $db;

    public function __construct(\Pmc\Database\MysqlDb $db)
    {
        $this->db = $db;
    }

    public function getAllStreams(): array
    {
        $stmt = $this->db->prepareStatement("SELECT * FROM events ORDER BY streamId, streamSeq");
        $rows = $this->db->fetchAllRows($stmt);
        return $rows;
    }

    public function getSerialisedStream(string $streamId): array
    {
        $stmt = $this->db->prepareStatement("SELECT * FROM events WHERE streamId = ? ORDER BY streamId, streamSeq");
        $stmt->bind_param("s", $streamId);
        $rows = $this->db->fetchAllRows($stmt);
        return $rows;
    }

    public function storeSerializedEvent(array $data): void
    {
        $now = microtime(true);
        $eventData = null;
        $metaData = null;
        $stmt = $this->db->prepareStatement("INSERT INTO events ("
                . "eventId, "
                . "streamId, "
                . "streamSeq, "
                . "eventName, "
                . "storedAt, "
                . "eventData, "
                . "metaData "
                . ") VALUES ("
                . "?,?,?, ?,?, ?,?"
                . ")");
        $stmt->bind_param("ssisdbb",
                $data['eventId'],
                $data['streamId'],
                $data['streamSeq'],
                $data['eventName'],
                $now,
                $eventData,
                $metaData
        );
        $stmt->send_long_data(5, $data['eventData']);
        $stmt->send_long_data(6, $data['metaData']);
        $this->db->executeNonReturn($stmt);
    }

}
