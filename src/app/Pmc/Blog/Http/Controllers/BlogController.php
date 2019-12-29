<?php

namespace Pmc\Blog\Http\Controllers;

use Pmc\Blog\AppContainer;
use Pmc\EsModules\Article\Query\ArticleBySlug;
use Pmc\EsModules\Article\Query\ArticleList;
use Pmc\ObjectLib\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class BlogController
{

    /**
     * @var AppContainer
     */
    private $container;

    

    public function __construct(AppContainer $container)
    {
        $this->container = $container;
    }
    
    public function getRoutes(): RouteCollection
    {
        $routes = new RouteCollection();
        $routes->add('index', new Route('/', [
            '_controller' => [$this, 'showIndex']
            ]));
        $routes->add('viewArticle', new Route('/article/{slug}', [
            '_controller' => [$this, 'showArticle']
            ]));
        return $routes;
    }


    public function showIndex()
    {
        $params = new ParameterBag([
            'className' => ArticleList::class,
            'includeUnpublished' => false
        ]);
        $query = $this->container->getFactory()->create($params);

        
        $this->container->getStatsdClient()->increment("articleList.hits", 0.2);
        
        return new Response(
                $this->container->twig()->render(
                        'articleList.twig',
                        ['articles' => $query->result()]
        ));
    }

    public function showArticle($slug)
    {
        $params = new ParameterBag([
            'className' => ArticleBySlug::class,
            'slug' => $slug
        ]);
        $query = $this->container->getFactory()->create($params);

        return new Response(
                $this->container->twig()->render('article.twig', [
                    'article' => $query->result()
        ]));
    }

}
