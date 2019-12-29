<?php

namespace Pmc\Blog\EventSource;

use InvalidArgumentException;
use Pmc\EsModules\Article\Event\ArticlePublished;
use Pmc\EsModules\Article\Event\BodyChanged;
use Pmc\EsModules\Article\Event\OpenGraphImageChanged;
use Pmc\EsModules\Article\Event\SlugChanged;
use Pmc\EsModules\Article\Event\SummaryChanged;
use Pmc\EsModules\Article\Event\TagsAdded;
use Pmc\EsModules\Article\Event\TagsRemoved;
use Pmc\EsModules\Article\Event\TitleChanged;
use Pmc\EsModules\User\Event\AuthenticationSucceeded;
use Pmc\EsModules\User\Event\LoginTokenCreated;
use Pmc\EsModules\User\Event\UserRegistered;
use Pmc\ObjectLib\ClassNameMap;

/**
 * @author Paul Court <emails@paulcourt.co.uk>
 */
class EventNameClassMap implements ClassNameMap
{

    private $map = [
        'User.Registered' => UserRegistered::class,
        'User.AuthenticationSucceeded' => AuthenticationSucceeded::class,
        'User.LoginTokenCreated' => LoginTokenCreated::class,
        
        'Article.OpenGraphImageChanged' => OpenGraphImageChanged::class,
        'Article.TitleChanged' => TitleChanged::class,
        'Article.SlugChanged' => SlugChanged::class,
        'Article.SummaryChanged' => SummaryChanged::class,
        'Article.BodyChanged' => BodyChanged::class,
        'Article.Published' => ArticlePublished::class,
        'Article.TagsAdded' => TagsAdded::class,
        'Article.TagsRemoved' => TagsRemoved::class,
    ];

    public function getClassForName(string $name): ?string
    {
        if (!isset($this->map[$name])) {
            throw new InvalidArgumentException("No map found for name: " . $name);
        }
        return $this->map[$name];
    }

    public function getNameForClass(string $className): ?string
    {
        $reverseMap = array_flip($this->map);
        if (!isset($reverseMap[$className])) {
            throw new InvalidArgumentException("No map found for class: " . $className);
        }
        return $reverseMap[$className];
    }

}
