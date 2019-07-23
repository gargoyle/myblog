<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Pmc\Blog\AppContainer;
use Pmc\Blog\Http\Controllers\BlogController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

define('START_TIME', microtime(true));
define('APP_ROOT', dirname(__DIR__));

session_start();

// Create out app container.
$app = new AppContainer();


// Setup core HTTP handling using symfony components.

$request = Request::createFromGlobals();
$context = new RequestContext();
$context->fromRequest($request);
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

$routes = new RouteCollection();

// Main view routes
$blogController = new BlogController($app);
$routes->addCollection($blogController->getRoutes());

// Article Admin Routes
$adminController = new Pmc\Blog\Http\Controllers\AdminController($app);
$adminRoutes = $adminController->getRoutes();
$adminRoutes->addPrefix('/admin');
$routes->addCollection($adminRoutes);

// Account controller
$accountController = new Pmc\Blog\Http\Controllers\AccountController($app);
$accountRoutes = $accountController->getRoutes();
$accountRoutes->addPrefix("/account");
$routes->addCollection($accountRoutes);

$matcher = new UrlMatcher($routes, $context);

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));

    $controller = $controllerResolver->getController($request);
    $arguments = $argumentResolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);
} catch (ResourceNotFoundException $e) {
    $response = new Response('Not Found', 404);
} catch (Exception $e) {
    $response = new Response('Oops: ' . $e->getMessage(), 500);
}

$response->setSharedMaxAge(3600);
//$response->headers->addCacheControlDirective('must-revalidate', true);
$response->send();
