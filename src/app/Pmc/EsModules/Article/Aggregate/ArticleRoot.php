<?php

namespace Pmc\EsModules\Article\Aggregate;

use DomainException;
use Pmc\EsModules\Article\Event\ArticlePublished;
use Pmc\EsModules\Article\Event\BodyChanged;
use Pmc\EsModules\Article\Event\OpenGraphImageChanged;
use Pmc\EsModules\Article\Event\SlugChanged;
use Pmc\EsModules\Article\Event\SummaryChanged;
use Pmc\EsModules\Article\Event\TagsAdded;
use Pmc\EsModules\Article\Event\TagsRemoved;
use Pmc\EsModules\Article\Event\TitleChanged;
use Pmc\EsModules\Article\ValueObject\Body;
use Pmc\EsModules\Article\ValueObject\Slug;
use Pmc\EsModules\Article\ValueObject\Summary;
use Pmc\EsModules\Article\ValueObject\Title;
use Pmc\EsModules\Article\ValueObject\Url;
use Pmc\EventSourceLib\Aggregate\AbstractRoot;
use Pmc\EventSourceLib\Event\DomainEvent;
use Pmc\EventSourceLib\Event\EventStream;

class ArticleRoot extends AbstractRoot
{
    /**
     *
     * @var float
     */
    private $created;
    
    /**
     *
     * @var float
     */
    private $published;
    
    /**
     *
     * @var Url
     */
    private $ogImageUrl;
    
    /**
     *
     * @var Title
     */
    private $title;

    /**
     *
     * @var Summary
     */
    private $summary;

    /**
     *
     * @var Body 
     */
    private $body;
    
    /**
     *
     * @var Slug
     */
    private $slug;
    
    /**
     *
     * @var array
     */
    private $tagList = [];

    
    protected function applyEvent(DomainEvent $event)
    {
        parent::applyEvent($event);
        if ($this->created == null) {
            $this->created = $event->getTimestamp();
        }
    }
    
    
    public function changeOpenGraphImage(Url $newUrl): EventStream
    {
        $this->raise(new OpenGraphImageChanged($this->id, $newUrl));
        return $this->pendingEvents;
    }
    
    public function applyOpenGraphImageChangedEvent(OpenGraphImageChanged $e)
    {
        $this->ogImageUrl = $e->getNewUrl();
    }
    
    
    public function changeTitle(Title $newTitle): EventStream
    {
        $this->raise(new TitleChanged($this->id, $newTitle));
        return $this->pendingEvents;
    }

    protected function applyTitleChangedEvent(TitleChanged $e)
    {
        $this->title = $e->getNewTitle();
    }
    

    public function changeSlug(Slug $newSlug): EventStream
    {
        $this->raise(new SlugChanged($this->id, $newSlug));
        return $this->pendingEvents;
    }

    protected function applySlugChangedEvent(SlugChanged $e)
    {
        $this->slug = $e->getNewSlug();
    }

    public function changeTags(string $newTags): EventStream
    {
        $arrNewTags = array_unique(explode(',', $newTags));
        array_walk($arrNewTags, function (&$val) { $val = trim($val); });        
        $tagsToAdd = array_diff($arrNewTags, $this->tagList);
        $tagsToRemove = array_diff($this->tagList, $arrNewTags);
        
        if (!empty($tagsToAdd)) {
            $this->raise(new TagsAdded($this->id, $tagsToAdd));
        }
        if (!empty($tagsToRemove)) {
            $this->raise(new TagsRemoved($this->id, $tagsToRemove));
        }
        
        return $this->pendingEvents;
    }
    
    
    protected function applyTagsAddedEvent(TagsAdded $e)
    {
        $newTagList = array_merge($this->tagList, $e->getTags());
        array_unique($newTagList);
        $this->tagList = $newTagList;
    }
        
    protected function applyTagsRemovedEvent(TagsRemoved $e)
    {
        $newTagList = array_diff($this->tagList, $e->getTags());
        array_unique($newTagList);
        $this->tagList = $newTagList;
    }
    
    
    public function changeSummary(Summary $newSummary): EventStream
    {
        $this->raise(new SummaryChanged($this->id, $newSummary));
        return $this->pendingEvents;
    }

    protected function applySummaryChangedEvent(SummaryChanged $e)
    {
        $this->summary = $e->getSummary();
    }

    
    public function changeBody(Body $newBody)
    {
        $this->raise(new BodyChanged($this->id, $newBody));
        return $this->pendingEvents;
    }

    protected function applyBodyChangedEvent(BodyChanged $e)
    {
        $this->body = $e->getBody();
    }

    
    public function publish()
    {
        $now = microtime(true);
        $yesterday = $now - 86400;
        
        if (($this->created == null) || ($this->created > $yesterday)) {
            throw new DomainException("Articles cannot be published within 24 hours of being created. (".$this->created.")");
        }
        
        $this->raise(new ArticlePublished($this->id));
        return $this->pendingEvents;
    }
    
    protected function applyArticlePublishedEvent(ArticlePublished $e)
    {
        if ($this->published == null) {
            $this->published = $e->getTimestamp();
        }
    }
}
