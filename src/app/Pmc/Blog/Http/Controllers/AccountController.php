<?php

namespace Pmc\Blog\Http\Controllers;

use DomainException;
use InvalidArgumentException;
use Pmc\Blog\AppContainer;
use Pmc\Database\RecordNotFoundException;
use Pmc\ObjectLib\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManager;

/**
 * Description of AccountController
 *
 * @author paul
 */
class AccountController
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
        
        $routes->add("accountRegister", new Route("/register", [
                    "_controller" => [$this, "registerAction"]
        ]));
        $routes->add("accountLogout", new Route("/logout", [
                    "_controller" => [$this, "logoutAction"]
        ]));
        $routes->add("accountLogin", new Route("/login", [
                    "_controller" => [$this, "loginAction"]
        ]));
        $routes->add("accountPassphraseReset", new Route("/ppreset", [
                    "_controller" => [$this, "ppResetAction"]
        ]));
        $routes->add("accountTokenLogin", new Route("/loginWithToken", [
                    "_controller" => [$this, "loginWithTokenAction"]
        ]));

        return $routes;
    }

    public function registerAction(Request $request)
    {
        $csrfTokenManager = new CsrfTokenManager();
        $csrfToken = $csrfTokenManager->getToken('user.account');
        $existingFormValues = [];
        $error = '';

        if ($request->isMethod('POST')) {
            try {
                $existingFormValues = $request->request->all();

                if (!$csrfTokenManager->isTokenValid(new CsrfToken('user.account', $request->get("csrfToken")))) {
                    throw new InvalidArgumentException("CSRF Token is invalid!");
                }

                $params = new ParameterBag();
                $params->add(['className' => 'User.Register']);
                foreach (['emailAddress', 'fullName', 'username', 'passphrase'] as $key) {
                    $params->add([$key => $request->get($key)]);
                }

                $command = $this->container->getFactory()->create($params);
                $this->container->getCommandBus()->dispatch($command);

                return new RedirectResponse('/');
            } catch (InvalidArgumentException $e) {
                $error = $e->getMessage();
            }
        }

        return new Response($this->container->twig()->render('register.twig', [
                    'csrfToken' => $csrfToken,
                    'existingValues' => $existingFormValues,
                    'error' => $error
        ]));
    }

    public function logoutAction()
    {
        session_destroy();
        return new RedirectResponse('/');
    }

    public function loginAction(Request $request)
    {
        $csrfTokenManager = new CsrfTokenManager();
        $csrfToken = $csrfTokenManager->getToken('user.account');
        $existingFormValues = [];
        $error = '';

        if ($request->isMethod('POST')) {
            try {
                $existingFormValues = $request->request->all();

                if (!$csrfTokenManager->isTokenValid(new CsrfToken('user.account', $request->get("csrfToken")))) {
                    throw new InvalidArgumentException("CSRF Token is invalid!");
                }

                $params = new ParameterBag();
                $params->add(['className' => 'User.AuthenticateWithPassphrase']);
                $params->add(['identification' => $request->get('identification')]);
                $params->add(['passphrase' => $request->get('passphrase')]);
                $command = $this->container->getFactory()->create($params);
                $this->container->getCommandBus()->dispatch($command);

                return new RedirectResponse('/');
            } catch (InvalidArgumentException | RecordNotFoundException $e) {
                $error = "Authentication failed: " . $e->getMessage();
            }
        }

        return new Response($this->container->twig()->render('login.twig', [
                    'csrfToken' => $csrfToken,
                    'existingValues' => $existingFormValues,
                    'error' => $error
        ]));
    }

    public function loginWithTokenAction(Request $request)
    {
        $csrfTokenManager = new CsrfTokenManager();
        $csrfToken = $csrfTokenManager->getToken('user.account');
        $existingFormValues = [];
        $error = '';

        if ($request->isMethod('POST')) {
            try {
                $existingFormValues = $request->request->all();

                if (!$csrfTokenManager->isTokenValid(new CsrfToken('user.account', $request->get("csrfToken")))) {
                    throw new InvalidArgumentException("CSRF Token is invalid!");
                }

                $params = new ParameterBag();
                $params->add(['className' => 'User.AuthenticateWithToken']);
                $params->add(['token' => $request->get('token')]);
                $command = $this->container->getFactory()->create($params);
                $this->container->getCommandBus()->dispatch($command);

                return new RedirectResponse('/');
            } catch (InvalidArgumentException | RecordNotFoundException $e) {
                $error = "Authentication failed";
            }
        }

        return new Response($this->container->twig()->render('login.twig', [
                    'csrfToken' => $csrfToken,
                    'existingValues' => $existingFormValues,
                    'error' => $error
        ]));
    }

    public function ppResetAction(Request $request)
    {
        $csrfTokenManager = new CsrfTokenManager();
        $csrfToken = $csrfTokenManager->getToken('user.account');
        $existingFormValues = [];
        $error = '';

        if ($request->isMethod('POST')) {
            try {
                $existingFormValues = $request->request->all();
                if (!$csrfTokenManager->isTokenValid(new CsrfToken('user.account', $request->get("csrfToken")))) {
                    throw new InvalidArgumentException("CSRF Token is invalid!");
                }

                $params = new ParameterBag();
                $params->add(['className' => 'User.CreateLoginToken']);
                $params->add(['identification' => $request->get('identification')]);
                $command = $this->container->getFactory()->create($params);
                $this->container->getCommandBus()->dispatch($command);
            } catch (DomainException | InvalidArgumentException | RecordNotFoundException $e) {
                $error = $e->getMessage();
            }
        }

        return new Response($this->container->twig()->render('login.twig', [
                    'csrfToken' => $csrfToken,
                    'existingValues' => $existingFormValues,
                    'error' => $error,
                    'displayMode' => 'ppreset'
        ]));
    }

}
