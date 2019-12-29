<?php

namespace Pmc\EsModules\Article\Handler;

use Pmc\Blog\EventSource\EventStore;
use Pmc\CommandBus\Handler;
use Pmc\EsModules\Article\Aggregate\ArticleRoot;
use Pmc\EsModules\Article\Command\ChangeBody;
use Pmc\EsModules\Article\Command\ChangeOpenGraphImage;
use Pmc\EsModules\Article\Command\ChangeSlug;
use Pmc\EsModules\Article\Command\ChangeSummary;
use Pmc\EsModules\Article\Command\ChangeTags;
use Pmc\EsModules\Article\Command\ChangeTitle;
use Pmc\EsModules\Article\Command\PublishArticle;

class ArticleHandler implements Handler
{

    /**
     * @var EventStore
     */
    private $eventStore;

    public function __construct(EventStore $eventStore)
    {
        $this->eventStore = $eventStore;
    }
    
    public function getSupportedCommands(): array
    {
        return [
            ChangeOpenGraphImage::class,
            ChangeTitle::class,
            ChangeSlug::class,
            ChangeSummary::class,
            ChangeBody::class,
            PublishArticle::class,
            ChangeTags::class
            ];
    }

    public function handleCommand($command): void
    {
        $article = new ArticleRoot($this->eventStore->getStream($command->getArticleId()));
        
        switch (true) {
            case $command instanceof ChangeOpenGraphImage:
                $events = $article->changeOpenGraphImage($command->getUrl());
                break;
            case $command instanceof ChangeTitle:
                $events = $article->changeTitle($command->getTitle());
                break;
            case $command instanceof ChangeSlug:
                $events = $article->changeSlug($command->getSlug());
                break;
            case $command instanceof ChangeSummary:
                $events = $article->changeSummary($command->getSummary());
                break;
            case $command instanceof ChangeBody:
                $events = $article->changeBody($command->getBody());
                break;
            case $command instanceof PublishArticle:
                $events = $article->publish();
                break;
            case $command instanceof ChangeTags:
                $events = $article->changeTags($command->getTags());
                break;
        }
        
        $this->eventStore->store($events);
    }

}