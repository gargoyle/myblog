<?php

namespace Pmc\Blog\Http\Controllers;

use DomainException;
use InvalidArgumentException;
use Pmc\Blog\AppContainer;
use Pmc\EsModules\Article\Query\ArticleById;
use Pmc\EsModules\Article\Query\ArticleList;
use Pmc\ObjectLib\Id;
use Pmc\ObjectLib\ParameterBag;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * 
 */
class AdminController
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
        
//        if ($this->container->getSession()->profile()->hasRole('Admin')) {
            $routes->add('adminIndex', new Route('/', [
                "_controller" => [$this, "indexAction"]
            ]));
            $routes->add('createArticle', new Route('/editarticle', [
                "_controller" => [$this, "editArticleAction"]
            ]));
            $routes->add('editArticle', new Route('/editarticle/{id}', [
                "_controller" => [$this, "editArticleAction"]
            ]));
            $routes->add("rebuildArticlesProjections", new Route('/rebuild', [
                "_controller" => [$this, "rebuildArticlesProjectionsAction"]
            ]));
//        }
        
        return $routes;
    }
    
    public function indexAction(Request $request)
    {
        $params = new ParameterBag([
            'className' => ArticleList::class,
            'includeUnpublished' => true
        ]);
        $query = $this->container->getFactory()->create($params);
        
        return new Response($this->container->twig()->render('admin/index.twig', [
           'articles' => $query->result()
        ]));    
    }

    public function editArticleAction(Request $request, string $id = '')
    {    
        // Load article view, or setup new id.
        try {
            $articleId = Id::fromString($id);
            $query = $this->container->getFactory()->create(new ParameterBag(['className' => ArticleById::class, 'articleId' => $id]));
            $currentValues = $query->result();
        } catch (InvalidUuidStringException $ex) {
            $articleId = new Id();
            $currentValues = [];
        }
        
        $csrfTokenManager = new CsrfTokenManager();
        $csrfToken = $csrfTokenManager->getToken('article.edit');
        if ($request->isMethod('POST')) {
            try {                
                if (!$csrfTokenManager->isTokenValid(new CsrfToken('article.edit', $request->get("csrfToken")))) {
                    throw new InvalidArgumentException("CSRF Token is invalid!");
                }

                $params = new ParameterBag($request->request->all());
                $command = $this->container->getFactory()->create($params);                
                $this->container->getCommandBus()->dispatch($command);

                return new Response('OK', 200);
                
            } catch (InvalidArgumentException|DomainException $e) {
                $this->container->getLogger()->warning(sprintf("Failed to process admin command: %s", $e->getMessage()));
                $this->container->getLogger()->debug("Failure Details", ['Stack Trace' => $e->getTraceAsString()]);
                
                return new Response('Failure', 400);
            }
        }
        
        
        return new Response($this->container->twig()->render('admin/editarticle.twig', [
            'articleId' => (string)$articleId,
            'currentValues' => $currentValues,
            'csrfToken' => $csrfToken,
            'error' => ""
        ]));    
    }
    
    public function rebuildArticlesProjectionsAction()
    {
        $this->container->rebuildProjections();
        return new RedirectResponse('/admin/');
    }
}
