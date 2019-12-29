<?php

namespace Pmc\EsModules\Article\Command;

use Pmc\EsModules\Article\Query\QueryFactory;
use Pmc\EsModules\Article\ValueObject\Body;
use Pmc\EsModules\Article\ValueObject\Slug;
use Pmc\EsModules\Article\ValueObject\Summary;
use Pmc\EsModules\Article\ValueObject\Title;
use Pmc\EsModules\Article\ValueObject\Url;
use Pmc\GigaFactory\Creator;
use Pmc\ObjectLib\Id;
use Pmc\ObjectLib\ParameterBag;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

class CommandFactory implements Creator
{

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    public function create(ParameterBag $params)
    {
        $params->require(['className']);
        switch ($params->get('className')) {
            case "Article.ChangeOpenGraphImage":
                return $this->createChangeOpenGraphImageCommand($params);
                break;
            case "Article.ChangeTitle":
                return $this->createChangeTitleCommand($params);
                break;
            case "Article.ChangeSlug":
                return $this->createChangeSlugCommand($params);
                break;
            case "Article.ChangeSummary":
                return $this->createChangeSummaryCommand($params);
                break;
            case 'Article.ChangeBody':
                return $this->createChangeBodyCommand($params);
                break;
            case 'Article.Publish':
                return $this->createPublishArticleCommand($params);
                break;
            case 'Article.ChangeTags':
                return $this->createChangeTagsCommand($params);
                break;
            default:
                throw new InvalidArgumentException(sprintf("Unable to create %s!", $params->get('commandName')));
                break;
        }
    }

    public function getCreatableList(): array
    {
        return [
            'Article.ChangeOpenGraphImage',
            'Article.ChangeTitle',
            'Article.ChangeSlug',
            'Article.ChangeSummary',
            'Article.ChangeBody',
            'Article.Publish',
            'Article.ChangeTags',
        ];
    }

    private function createChangeOpenGraphImageCommand(ParameterBag $params): ChangeOpenGraphImage
    {
        $params->require(['articleId', 'openGraphImageUrl']);
        return new ChangeOpenGraphImage(
                Id::fromString($params->get('articleId')),
                new Url($params->get('openGraphImageUrl')));
    }
    
    private function createChangeTitleCommand(ParameterBag $params): ChangeTitle
    {
        $params->require(['articleId', 'articleTitle']);
        return new ChangeTitle(
                Id::fromString($params->get('articleId')),
                new Title($params->get('articleTitle')));
    }

    private function createChangeSlugCommand(ParameterBag $params): ChangeSlug
    {
        $params->require(['articleId', 'articleSlug']);
        return new ChangeSlug(
                Id::fromString($params->get('articleId')),
                new Slug($params->get('articleSlug')));
    }

    private function createChangeSummaryCommand(ParameterBag $params): ChangeSummary
    {
        $params->require(['articleId', 'articleSummary']);
        return new ChangeSummary(
                Id::fromString($params->get('articleId')),
                new Summary($params->get('articleSummary')));
    }

    private function createChangeBodyCommand(ParameterBag $params): ChangeBody
    {
        
        $params->require(['articleId', 'articleBody']);
        return new ChangeBody(
                Id::fromString($params->get('articleId')),
                new Body($params->get('articleBody')));
    }
    
    private function createPublishArticleCommand(ParameterBag $params): PublishArticle
    {
        $params->require(['articleId']);
        return new PublishArticle(Id::fromString($params->get('articleId')));
    }

    public function createChangeTagsCommand(ParameterBag $params): ChangeTags
    {
        $params->require(['articleId', 'tags']);
        return new ChangeTags(Id::fromString($params->get('articleId')), 
                (string)$params->get('tags'));
    }

}
