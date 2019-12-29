<?php

namespace Pmc\EsModules\Article\Projection;

use Pmc\Database\MysqlDb;
use Pmc\EsModules\Article\Event\ArticlePublished;
use Pmc\EsModules\Article\Event\BodyChanged;
use Pmc\EsModules\Article\Event\OpenGraphImageChanged;
use Pmc\EsModules\Article\Event\SlugChanged;
use Pmc\EsModules\Article\Event\SummaryChanged;
use Pmc\EsModules\Article\Event\TagsAdded;
use Pmc\EsModules\Article\Event\TagsRemoved;
use Pmc\EsModules\Article\Event\TitleChanged;
use Pmc\EventSourceLib\Event\EventStream;
use Pmc\MessageBus\Listener;

class Articles implements Listener
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
            OpenGraphImageChanged::class,
            TitleChanged::class,
            SlugChanged::class,
            SummaryChanged::class,
            BodyChanged::class,
            ArticlePublished::class,
            TagsAdded::class,
            TagsRemoved::class
        ];
    }

    public function notify($message): void
    {
        $this->createTableIfNeeded();

        switch (get_class($message)) {
            case OpenGraphImageChanged::class:
                $this->updateOpenGraphImage($message);
                break;
            case TitleChanged::class:
                $this->updateTitle($message);
                break;
            case SlugChanged::class:
                $this->updateSlug($message);
                break;
            case SummaryChanged::class:
                $this->updateSummary($message);
                break;
            case BodyChanged::class:
                $this->updateBody($message);
                break;
            case ArticlePublished::class:
                $this->updatePublished($message);
                break;
            case TagsAdded::class:
                $this->addTags($message);
                break;
            case TagsRemoved::class:
                $this->removeTags($message);
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
        $sql = 'DROP TABLE IF EXISTS articles';
        $stmt = $this->db->prepareStatement($sql);
        $this->db->executeNonReturn($stmt);

        $sql = 'DROP TABLE IF EXISTS articleTags';
        $stmt = $this->db->prepareStatement($sql);
        $this->db->executeNonReturn($stmt);
        $this->tableCreated = false;
    }

    private function createTableIfNeeded(): void
    {
        if ($this->tableCreated) {
            return;
        }

        $sql = ''
                . 'CREATE TABLE IF NOT EXISTS articles ('
                . '`articleId` char(64) COLLATE utf8mb4_bin NOT NULL,'
                . '`created` DECIMAL(18,4) NOT NULL,'
                . '`lastUpdated` DECIMAL(18,4) NOT NULL,'
                . '`published` DECIMAL(18,4) NULL,'
                . '`openGraphImageUrl` char(255) COLLATE utf8mb4_unicode_ci NULL,'
                . '`title` char(100) COLLATE utf8mb4_unicode_ci NULL,'
                . '`slug` char(100) COLLATE utf8mb4_unicode_ci NULL,'
                . '`summary` text COLLATE utf8mb4_unicode_ci NULL,'
                . '`body` text COLLATE utf8mb4_unicode_ci NULL,'
                . 'PRIMARY KEY (`articleId`)'
                . ');';
        $stmt = $this->db->prepareStatement($sql);
        $this->db->executeNonReturn($stmt);

        $sql = ''
                . 'CREATE TABLE IF NOT EXISTS articleTags ('
                . '`articleId` char(64) COLLATE utf8mb4_bin NOT NULL,'
                . '`tag` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,'
                . 'PRIMARY KEY (`articleId`, `tag`)'
                . ');';
        $stmt = $this->db->prepareStatement($sql);
        $this->db->executeNonReturn($stmt);

        $this->tableCreated = true;
    }

    private function updateOpenGraphImage(OpenGraphImageChanged $e)
    {
        $newUrl = $e->getNewUrl();
        $articleId = $e->getArticleId();
        $created = $e->getTimestamp();
        $lastUpdated = microtime(true);

        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated, openGraphImageUrl)"
                . "VALUES "
                . "     (?, ?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated), openGraphImageUrl=VALUES(openGraphImageUrl)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sdds', $articleId, $created, $lastUpdated, $newUrl);
        $this->db->executeNonReturn($stmt);
    }

    private function updateTitle(TitleChanged $e)
    {
        $newTitle = $e->getNewTitle();
        $articleId = $e->getArticleId();
        $created = $e->getTimestamp();
        $lastUpdated = microtime(true);

        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated, title)"
                . "VALUES "
                . "     (?, ?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated), title=VALUES(title)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sdds', $articleId, $created, $lastUpdated, $newTitle);
        $this->db->executeNonReturn($stmt);
    }

    private function updateSlug(SlugChanged $e)
    {
        $newSlug = $e->getNewSlug();
        $articleId = $e->getArticleId();
        $created = $e->getTimestamp();
        $lastUpdated = microtime(true);

        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated, slug)"
                . "VALUES "
                . "     (?, ?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated), slug=VALUES(slug)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sdds', $articleId, $created, $lastUpdated, $newSlug);
        $this->db->executeNonReturn($stmt);
    }

    private function updateSummary(SummaryChanged $e)
    {
        $newSummary = $e->getSummary();
        $articleId = $e->getArticleId();
        $created = $e->getTimestamp();
        $lastUpdated = microtime(true);

        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated, summary)"
                . "VALUES "
                . "     (?, ?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated), summary=VALUES(summary)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sdds', $articleId, $created, $lastUpdated,
                $newSummary);
        $this->db->executeNonReturn($stmt);
    }

    private function updateBody(BodyChanged $e)
    {
        $newBody = $e->getBody();
        $articleId = $e->getArticleId();
        $created = $e->getTimestamp();
        $lastUpdated = microtime(true);

        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated, body)"
                . "VALUES "
                . "     (?, ?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated), body=VALUES(body)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sdds', $articleId, $created, $lastUpdated, $newBody);
        $this->db->executeNonReturn($stmt);
    }

    private function updatePublished(ArticlePublished $e)
    {
        $publishedOn = $e->getTimestamp();
        $articleId = $e->getArticleId();
        $created = $e->getTimestamp();
        $lastUpdated = microtime(true);

        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated, published)"
                . "VALUES "
                . "     (?, ?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated), published=VALUES(published)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sddd', $articleId, $created, $lastUpdated,
                $publishedOn);
        $this->db->executeNonReturn($stmt);
    }

    private function addTags(TagsAdded $e)
    {
        $tags = $e->getTags();
        if (empty($tags)) {
            return;
        }

        $articleId = $e->getArticleId();
        $lastUpdated = microtime(true);
        $created = $e->getTimestamp();
        
        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated)"
                . "VALUES "
                . "     (?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sdd', $articleId, $created, $lastUpdated);
        $this->db->executeNonReturn($stmt);

        $sql = "INSERT INTO articleTags (articleId, tag) VALUES (?, ?)";
        $stmt = $this->db->prepareStatement($sql);
        foreach ($tags as $tag) {
            $stmt->bind_param('ss', $articleId, $tag);
            $this->db->executeNonReturn($stmt, false);
        }
        $stmt->close();
    }

    private function removeTags(TagsRemoved $e)
    {
        $tags = $e->getTags();
        if (empty($tags)) {
            return;
        }

        $articleId = $e->getArticleId();
        $lastUpdated = microtime(true);
        $created = $e->getTimestamp();
        
        $sql = "INSERT INTO articles "
                . "     (articleId, created, lastUpdated)"
                . "VALUES "
                . "     (?, ?, ?) "
                . "ON DUPLICATE KEY UPDATE "
                . "     lastUpdated=VALUES(lastUpdated)";
        $stmt = $this->db->prepareStatement($sql);
        $stmt->bind_param('sdd', $articleId, $created, $lastUpdated);
        $this->db->executeNonReturn($stmt);

        $sql = "DELETE FROM articleTags WHERE articleId = ? AND tag = ?";
        $stmt = $this->db->prepareStatement($sql);
        foreach ($tags as $tag) {
            $stmt->bind_param('ss', $articleId, $tag);
            $this->db->executeNonReturn($stmt, false);
        }
        $stmt->close();
    }

}
